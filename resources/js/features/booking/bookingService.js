import { http, query, ApiError, ApiConflict } from '../../services/http.js';
import { mockBookingApi } from './mockBookingData.js';

/**
 * Booking feature service.
 *
 * Pages and components call this and nothing else. Whether the data comes from
 * Laravel or from the in-browser mock is decided here, in one place, so switching
 * over later touches no component.
 *
 * Set VITE_USE_MOCK_BOOKINGS=false in .env once the Laravel booking endpoints
 * exist. It defaults to the mock because those endpoints are not built yet.
 */

const flag = import.meta.env.VITE_USE_MOCK_BOOKINGS;

export const usingMockBookings = flag === undefined || flag === '' || flag === 'true';

export { ApiError, ApiConflict };

/** The signed-in identity, passed to the mock so it knows who is acting.
 *  Against the real API this is irrelevant — Laravel reads the identity from the
 *  verified Firebase token in the Authorization header, never from us. */
function identityFrom(user) {
    if (!user) return null;

    return { email: user.email, name: user.displayName || null };
}

/** Translate a mock rejection into the same error types the real transport throws. */
function rethrow(error) {
    if (error?.isMockHttpError) {
        if (error.status === 409) {
            throw new ApiConflict(error.message, error.code);
        }

        throw new ApiError(error.message, error.status, error.code);
    }

    throw error;
}

async function viaMock(operation) {
    try {
        return await operation();
    } catch (error) {
        rethrow(error);
    }
}

export const bookingService = {
    /**
     * FR-06 — live availability for one date.
     *
     * The response carries every cell's state already decided by the backend. The
     * grid renders it; it must never work the state out from raw bookings itself,
     * or the two implementations will drift and the drift will look like a bug.
     */
    availability(date, user) {
        if (usingMockBookings) {
            return viaMock(() => mockBookingApi.availability(date, identityFrom(user)));
        }

        return http.get(`/availability${query({ date })}`);
    },

    /** FR-09 — only coaches who published availability for this exact cell. */
    availableCoaches(date, timeSlotId, user) {
        if (usingMockBookings) {
            return viaMock(() => mockBookingApi.availableCoaches(date, timeSlotId, identityFrom(user)));
        }

        return http.get(`/coaches/available${query({ date, time_slot_id: timeSlotId })}`);
    },

    /** FR-07 / FR-08 / FR-09 — booking_type is OPEN, LOCKED or PRIVATE_COACH. */
    createBooking(payload, user) {
        if (usingMockBookings) {
            return viaMock(() => mockBookingApi.createBooking(payload, identityFrom(user)));
        }

        return http.post('/bookings', payload);
    },

    /** FR-10 / FR-11 — adds a participant to the existing booking. */
    joinBooking(bookingId, user) {
        if (usingMockBookings) {
            return viaMock(() => mockBookingApi.joinBooking(bookingId, identityFrom(user)));
        }

        return http.post(`/bookings/${bookingId}/join`);
    },

    /** FR-15 — scope is 'upcoming', 'past' or 'cancelled'. */
    myBookings(scope, user) {
        if (usingMockBookings) {
            return viaMock(() => mockBookingApi.myBookings(scope, identityFrom(user)));
        }

        return http.get(`/bookings/mine${query({ scope })}`);
    },

    /** FR-16 — cancel your own booking, or leave someone else's open session. */
    cancelBooking(bookingId, user) {
        if (usingMockBookings) {
            return viaMock(() => mockBookingApi.cancelBooking(bookingId, identityFrom(user)));
        }

        return http.post(`/bookings/${bookingId}/cancel`);
    },
};
