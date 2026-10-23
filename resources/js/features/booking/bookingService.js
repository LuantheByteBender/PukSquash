import { http, query, ApiError, ApiConflict } from '../../services/http.js';

/**
 * Booking feature service.
 *
 * Pages and components call this and nothing else. Every operation goes to the
 * Laravel API — there is no client-side data source any more, and there should
 * not be one again. The browser asks; Laravel and PostgreSQL decide (BR-13).
 *
 * The endpoint contract the backend must satisfy is in DOCUMENTATION_README.md
 * in this folder. Each method below names the functional requirement it serves.
 *
 * The `user` argument is accepted but never sent. Identity travels only as the
 * Firebase ID token in the Authorization header, which `services/http.js`
 * attaches — a user id in a request body could be forged by a modified client.
 * It stays in the signature so callers do not all have to change if a future
 * operation genuinely needs it.
 */

export { ApiError, ApiConflict };

export const usingMockBookings = false;

export const bookingService = {
    /**
     * FR-06 — live availability for one date.
     *
     * The response must carry every cell's state already decided by the backend.
     * The grid renders it; it must never work the state out from raw bookings
     * itself, or the two implementations will drift and the drift will look
     * exactly like a booking bug.
     */
    availability(date) {
        return http.get(`/availability${query({ date })}`);
    },

    /** FR-09 — only coaches who published availability for this exact cell. */
    availableCoaches(date, timeSlotId) {
        return http.get(`/coaches/available${query({ date, time_slot_id: timeSlotId })}`);
    },

    coachNoticeBoard(date) {
        return http.get(`/notice-board/coaches${query({ date })}`);
    },

    /** FR-07 / FR-08 / FR-09 — booking_type is OPEN, LOCKED or PRIVATE_COACH. */
    createBooking(payload) {
        return http.post('/bookings', payload);
    },

    /** FR-10 / FR-11 — adds a participant to the existing booking. */
    joinBooking(bookingId) {
        return http.post(`/bookings/${bookingId}/join`);
    },

    /** FR-15 — scope is 'upcoming', 'past' or 'cancelled'. */
    myBookings(scope) {
        return http.get(`/bookings/mine${query({ scope })}`);
    },

    /** Coach — view upcoming coaching sessions booked with you. */
    myCoachBookingsUpcoming() {
        return http.get('/coach/bookings/upcoming');
    },

    /** FR-16 — cancel your own booking, or leave someone else's open session. */
    cancelBooking(bookingId) {
        return http.post(`/bookings/${bookingId}/cancel`);
    },

    coachTeamPracticeAvailability(date) {
        return http.get(`/coach/team-practice/availability${query({ date })}`);
    },

    createTeamPractice(selections) {
        return http.post('/coach/team-practices', { selections });
    },

    myTeamPractices(date) {
        return http.get(`/coach/team-practices${query({ date })}`);
    },
};
