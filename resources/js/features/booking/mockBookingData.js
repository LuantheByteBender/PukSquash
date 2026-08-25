/**
 * In-browser stand-in for the booking API.
 *
 * WHY THIS EXISTS
 * The booking endpoints do not exist in Laravel yet, and `dataconnect/schema/schema.gql`
 * cannot currently store the approved booking model (no time slots, no booking_type, no
 * booking players, no coach availability). This module implements the model the approved
 * v2.0 requirements describe, so the booking interface can be built, demonstrated and
 * reviewed now — and so the shape of the data the backend must return is written down
 * somewhere executable.
 *
 * WHAT IT IS NOT
 * It is not a security boundary and it is not the source of truth. It runs in the
 * browser, where anyone can edit it. The real rules belong in Laravel and PostgreSQL.
 * The duplicated checks below exist purely so the interface has to handle the same
 * failures it will meet in production — the 409 conflicts especially.
 *
 * Delete this file once the Laravel endpoints are live and
 * VITE_USE_MOCK_BOOKINGS is switched off.
 */

const STORE_KEY = 'puksquash.mock.bookings.v1';
const LATENCY_MS = 180;

/* -------------------------------------------------------------------------
 * Phase 1 booking rules.
 *
 * BR-14 says these are hard-coded in the backend. The approved requirements
 * never specify the numbers, and say explicitly that they must be confirmed
 * with the client rather than invented. These are PLACEHOLDERS.
 * ------------------------------------------------------------------------- */
export const PHASE1_RULES = {
    bookingWindowDays: 14, // TODO confirm with the Head Squash Coach
    maxActiveBookings: 2, // TODO confirm with the Head Squash Coach
    cancellationCutoffHours: 2, // TODO confirm with the Head Squash Coach
};

/* ------------------------------------------------------------------ utils -- */

const uuid = () =>
    crypto.randomUUID
        ? crypto.randomUUID()
        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (ch) => {
              const r = (Math.random() * 16) | 0;
              return (ch === 'x' ? r : (r & 0x3) | 0x8).toString(16);
          });

const today = () => new Date().toISOString().slice(0, 10);

const addDays = (iso, days) => {
    const date = new Date(`${iso}T00:00:00`);
    date.setDate(date.getDate() + days);
    return date.toISOString().slice(0, 10);
};

const nowIso = () => new Date().toISOString();

class MockHttpError extends Error {
    constructor(status, message, code) {
        super(message);
        this.status = status;
        this.code = code;
        this.isMockHttpError = true;
    }
}

const conflict = (message, code) => new MockHttpError(409, message, code);

/* ------------------------------------------------------------------- seed -- */

const SLOT_TIMES = [
    ['07:00', '08:00'], ['08:00', '09:00'], ['09:00', '10:00'], ['10:00', '11:00'],
    ['11:00', '12:00'], ['12:00', '13:00'], ['13:00', '14:00'], ['14:00', '15:00'],
    ['15:00', '16:00'], ['16:00', '17:00'], ['17:00', '18:00'], ['18:00', '19:00'],
    ['19:00', '20:00'], ['20:00', '21:00'],
];

function seed() {
    const timeSlots = SLOT_TIMES.map(([startTime, endTime], index) => ({
        id: uuid(),
        start_time: startTime,
        end_time: endTime,
        display_order: index + 1,
        is_active: true,
    }));

    // Six courts, per the Business Case. Court 6 starts INACTIVE so the
    // UNAVAILABLE cell state is visible without an admin having to change anything.
    const courts = [1, 2, 3, 4, 5, 6].map((n) => ({
        id: uuid(),
        name: `Court ${n}`,
        description: n === 6 ? 'Back court — floor resurfacing' : '',
        status: n === 6 ? 'INACTIVE' : 'ACTIVE',
    }));

    const users = [
        ['Karla Pienaar', 'karla@mynwu.ac.za', 'STUDENT'],
        ['Marco Welman', 'marco@mynwu.ac.za', 'STUDENT'],
        ['Head Squash Coach', 'coach@nwu.ac.za', 'COACH'],
        ['Assistant Coach', 'coach2@nwu.ac.za', 'COACH'],
    ].map(([name, email, role]) => ({ id: uuid(), name, email, role }));

    const db = {
        users,
        courts,
        time_slots: timeSlots,
        bookings: [],
        booking_players: [],
        coach_availability: [],
        force_conflict: false,
    };

    const [karla, marco, coach, coach2] = users;
    const date = today();

    // Coach availability for today and tomorrow.
    for (const day of [date, addDays(date, 1)]) {
        for (const slot of timeSlots.filter((s) =>
            ['15:00', '16:00', '17:00'].includes(s.start_time)
        )) {
            db.coach_availability.push({
                id: uuid(),
                coach_id: coach.id,
                time_slot_id: slot.id,
                availability_date: day,
            });
        }

        for (const slot of timeSlots.filter((s) =>
            ['17:00', '18:00'].includes(s.start_time)
        )) {
            db.coach_availability.push({
                id: uuid(),
                coach_id: coach2.id,
                time_slot_id: slot.id,
                availability_date: day,
            });
        }
    }

    const makeBooking = (courtIndex, slotStart, type, creator, second, coachId) => {
        const slot = timeSlots.find((s) => s.start_time === slotStart);

        const booking = {
            id: uuid(),
            court_id: courts[courtIndex].id,
            time_slot_id: slot.id,
            created_by_user_id: creator.id,
            coach_id: coachId ?? null,
            booking_date: date,
            booking_type: type,
            status: 'CONFIRMED',
            booked_at: nowIso(),
            cancelled_at: null,
        };

        db.bookings.push(booking);

        db.booking_players.push({
            id: uuid(),
            booking_id: booking.id,
            user_id: creator.id,
            player_position: 1,
            joined_at: nowIso(),
        });

        if (second) {
            db.booking_players.push({
                id: uuid(),
                booking_id: booking.id,
                user_id: second.id,
                player_position: 2,
                joined_at: nowIso(),
            });
        }
    };

    makeBooking(0, '17:00', 'OPEN', karla); // joinable
    makeBooking(1, '17:00', 'OPEN', marco, karla); // full
    makeBooking(2, '18:00', 'LOCKED', karla); // locked
    makeBooking(1, '15:00', 'PRIVATE_COACH', marco, null, coach.id);

    return db;
}

/* ---------------------------------------------------------------- storage -- */

let db = null;

function load() {
    if (db) return db;

    try {
        const raw = localStorage.getItem(STORE_KEY);

        if (raw) {
            db = JSON.parse(raw);
            return db;
        }
    } catch {
        // Storage blocked (private window) — fall through to an in-memory db.
    }

    db = seed();
    save();

    return db;
}

function save() {
    try {
        localStorage.setItem(STORE_KEY, JSON.stringify(db));
    } catch {
        // Ignore — the mock still works for this page view.
    }
}

export function resetMockData() {
    db = seed();
    save();
}

/** Dev only: arm the next booking attempt to fail with a 409, so the conflict
 *  path can be demonstrated without opening two browsers. */
export function armConflict(on = true) {
    load().force_conflict = on;
    save();
}

export function conflictArmed() {
    return load().force_conflict;
}

/* -------------------------------------------------------------- accessors -- */

const userById = (id) => load().users.find((u) => u.id === id) || null;
const slotById = (id) => load().time_slots.find((s) => s.id === id) || null;
const courtById = (id) => load().courts.find((c) => c.id === id) || null;

/** Resolve the acting identity, creating a demo user on first use. */
function actingUser(identity) {
    const data = load();

    const email = (identity?.email || 'demo.student@mynwu.ac.za').toLowerCase();

    let user = data.users.find((u) => u.email.toLowerCase() === email);

    if (!user) {
        user = {
            id: uuid(),
            name: identity?.name || 'Demo Student',
            email,
            role: 'STUDENT',
        };

        data.users.push(user);
        save();
    }

    return user;
}

const playersFor = (bookingId) =>
    load()
        .booking_players.filter((p) => p.booking_id === bookingId)
        .sort((a, b) => a.player_position - b.player_position)
        .map((p) => ({ ...p, user: userById(p.user_id) }));

const confirmedFor = (courtId, date, slotId) =>
    load().bookings.find(
        (b) =>
            b.status === 'CONFIRMED' &&
            b.court_id === courtId &&
            b.booking_date === date &&
            b.time_slot_id === slotId
    );

const firstName = (user) => (user?.name || 'Player').split(' ')[0];

/* --------------------------------------------------- cell state derivation -- */
/* Mirrors §8.1 of the detailed requirements. The interface never does this
   itself — it renders whatever state it is given. */

function cellFor(court, slot, date, meId) {
    const base = { court_id: court.id, time_slot_id: slot.id };

    if (court.status !== 'ACTIVE' || !slot.is_active) {
        return {
            ...base,
            state: 'UNAVAILABLE',
            label: court.status !== 'ACTIVE' ? 'Court inactive' : 'Slot disabled',
            detail: court.description || null,
            can_book: false,
            can_join: false,
        };
    }

    const booking = confirmedFor(court.id, date, slot.id);

    if (!booking) {
        return { ...base, state: 'AVAILABLE', label: 'Available', can_book: true, can_join: false };
    }

    const players = playersFor(booking.id);
    const mine = players.some((p) => p.user_id === meId);

    const common = {
        ...base,
        booking_id: booking.id,
        booking_type: booking.booking_type,
        is_mine: mine,
        players: players.map((p) => ({
            position: p.player_position,
            name: p.user?.name || 'Player',
        })),
        can_book: false,
        can_join: false,
    };

    if (booking.booking_type === 'OPEN') {
        if (players.length < 2) {
            return {
                ...common,
                state: 'OPEN',
                label: `${firstName(players[0]?.user)} VS —`,
                detail: 'Open — one player may join',
                can_join: !mine,
            };
        }

        return {
            ...common,
            state: 'FULL',
            label: `${firstName(players[0]?.user)} VS ${firstName(players[1]?.user)}`,
            detail: 'Full',
        };
    }

    if (booking.booking_type === 'LOCKED') {
        return {
            ...common,
            state: 'LOCKED',
            label: `${firstName(players[0]?.user)} — Locked`,
            detail: 'Private session',
        };
    }

    const coach = userById(booking.coach_id);

    return {
        ...common,
        state: 'PRIVATE_COACH',
        label: `${firstName(players[0]?.user)} + Coach`,
        detail: coach ? `Coach ${coach.name}` : 'Private coaching',
        coach: coach ? { id: coach.id, name: coach.name } : null,
    };
}

/* ----------------------------------------------------------- rule checking -- */

function assertBookableDate(date) {
    const start = today();

    if (date < start) {
        throw new MockHttpError(422, 'You cannot book a date in the past.');
    }

    if (date > addDays(start, PHASE1_RULES.bookingWindowDays)) {
        throw new MockHttpError(
            422,
            `Bookings open ${PHASE1_RULES.bookingWindowDays} days in advance.`
        );
    }
}

function assertUnderBookingLimit(user) {
    const data = load();
    const start = today();

    const active = data.bookings.filter(
        (b) =>
            b.status === 'CONFIRMED' &&
            b.booking_date >= start &&
            data.booking_players.some((p) => p.booking_id === b.id && p.user_id === user.id)
    );

    if (active.length >= PHASE1_RULES.maxActiveBookings) {
        throw new MockHttpError(
            422,
            `You already have ${PHASE1_RULES.maxActiveBookings} upcoming bookings. Cancel one before making another.`
        );
    }
}

function withinCancellationCutoff(date, slot) {
    const start = new Date(`${date}T${slot.start_time}:00`);

    return start.getTime() - Date.now() < PHASE1_RULES.cancellationCutoffHours * 3600 * 1000;
}

function expand(booking) {
    const court = courtById(booking.court_id);
    const slot = slotById(booking.time_slot_id);
    const coach = booking.coach_id ? userById(booking.coach_id) : null;

    return {
        id: booking.id,
        booking_date: booking.booking_date,
        booking_type: booking.booking_type,
        status: booking.status,
        court: court ? { id: court.id, name: court.name } : null,
        time_slot: slot
            ? { id: slot.id, start_time: slot.start_time, end_time: slot.end_time }
            : null,
        coach: coach ? { id: coach.id, name: coach.name } : null,
        created_by_user_id: booking.created_by_user_id,
        players: playersFor(booking.id).map((p) => ({
            position: p.player_position,
            user_id: p.user_id,
            name: p.user?.name || 'Player',
        })),
    };
}

const delay = () => new Promise((resolve) => setTimeout(resolve, LATENCY_MS));

/* ------------------------------------------------------------- operations -- */

export const mockBookingApi = {
    async availability(date, identity) {
        await delay();

        const data = load();
        const me = actingUser(identity);
        const day = date || today();

        const courts = data.courts
            .slice()
            .sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true }));

        const slots = data.time_slots
            .slice()
            .sort((a, b) => a.display_order - b.display_order);

        const cells = [];

        for (const slot of slots) {
            for (const court of courts) {
                cells.push(cellFor(court, slot, day, me.id));
            }
        }

        return {
            date: day,
            booking_window_days: PHASE1_RULES.bookingWindowDays,
            courts,
            time_slots: slots,
            cells,
        };
    },

    async availableCoaches(date, timeSlotId) {
        await delay();

        const data = load();

        return data.coach_availability
            .filter((a) => a.availability_date === date && a.time_slot_id === timeSlotId)
            .map((a) => userById(a.coach_id))
            .filter((c) => c && c.role === 'COACH')
            // BR-13 — a coach already booked for this slot is not offered.
            .filter(
                (c) =>
                    !data.bookings.some(
                        (b) =>
                            b.status === 'CONFIRMED' &&
                            b.coach_id === c.id &&
                            b.booking_date === date &&
                            b.time_slot_id === timeSlotId
                    )
            )
            .map((c) => ({ id: c.id, name: c.name }));
    },

    async createBooking(payload, identity) {
        await delay();

        const data = load();
        const me = actingUser(identity);

        const { court_id, time_slot_id, booking_date, booking_type, coach_id } = payload;

        if (!['OPEN', 'LOCKED', 'PRIVATE_COACH'].includes(booking_type)) {
            throw new MockHttpError(422, 'Choose a session type.');
        }

        const court = courtById(court_id);
        const slot = slotById(time_slot_id);

        if (!court || !slot) {
            throw new MockHttpError(404, 'That court or time slot no longer exists.');
        }

        if (court.status !== 'ACTIVE') {
            throw new MockHttpError(422, `${court.name} is not currently available for booking.`);
        }

        assertBookableDate(booking_date);
        assertUnderBookingLimit(me);

        if (data.force_conflict) {
            data.force_conflict = false;
            save();
            throw conflict(
                'That slot was just taken by another student. The grid has been refreshed.',
                'SLOT_TAKEN'
            );
        }

        // BR-06 — final re-check immediately before the write (§13 step 5).
        if (confirmedFor(court.id, booking_date, slot.id)) {
            throw conflict(
                'That slot was just taken by another student. The grid has been refreshed.',
                'SLOT_TAKEN'
            );
        }

        const clash = data.bookings.some(
            (b) =>
                b.status === 'CONFIRMED' &&
                b.booking_date === booking_date &&
                b.time_slot_id === slot.id &&
                data.booking_players.some((p) => p.booking_id === b.id && p.user_id === me.id)
        );

        if (clash) {
            throw new MockHttpError(422, 'You already have a booking at that time.');
        }

        let coach = null;

        if (booking_type === 'PRIVATE_COACH') {
            coach = userById(coach_id);

            if (!coach || coach.role !== 'COACH') {
                throw new MockHttpError(422, 'Select an available coach.');
            }

            const published = data.coach_availability.some(
                (a) =>
                    a.coach_id === coach.id &&
                    a.availability_date === booking_date &&
                    a.time_slot_id === slot.id
            );

            if (!published) {
                throw conflict(
                    'That coach is no longer available for this slot.',
                    'COACH_UNAVAILABLE'
                );
            }

            const busy = data.bookings.some(
                (b) =>
                    b.status === 'CONFIRMED' &&
                    b.coach_id === coach.id &&
                    b.booking_date === booking_date &&
                    b.time_slot_id === slot.id
            );

            if (busy) {
                throw conflict('That coach was just booked for this slot.', 'COACH_UNAVAILABLE');
            }
        }

        const booking = {
            id: uuid(),
            court_id: court.id,
            time_slot_id: slot.id,
            created_by_user_id: me.id,
            coach_id: coach?.id ?? null,
            booking_date,
            booking_type,
            status: 'CONFIRMED',
            booked_at: nowIso(),
            cancelled_at: null,
        };

        data.bookings.push(booking);

        data.booking_players.push({
            id: uuid(),
            booking_id: booking.id,
            user_id: me.id,
            player_position: 1,
            joined_at: nowIso(),
        });

        save();

        return expand(booking);
    },

    async joinBooking(bookingId, identity) {
        await delay();

        const data = load();
        const me = actingUser(identity);
        const booking = data.bookings.find((b) => b.id === bookingId);

        if (!booking || booking.status !== 'CONFIRMED') {
            throw conflict('That booking is no longer available to join.', 'BOOKING_GONE');
        }

        if (booking.booking_type !== 'OPEN') {
            // BR-10 — a locked booking cannot be joined.
            throw new MockHttpError(422, 'That booking is private and cannot be joined.');
        }

        const players = playersFor(booking.id);

        if (players.some((p) => p.user_id === me.id)) {
            throw new MockHttpError(422, 'You are already in this booking.');
        }

        if (data.force_conflict) {
            data.force_conflict = false;
            save();
            throw conflict('Another student took the second player spot first.', 'BOOKING_FULL');
        }

        // BR-09 — capacity is two players.
        if (players.length >= 2) {
            throw conflict('Another student took the second player spot first.', 'BOOKING_FULL');
        }

        assertBookableDate(booking.booking_date);
        assertUnderBookingLimit(me);

        const clash = data.bookings.some(
            (b) =>
                b.status === 'CONFIRMED' &&
                b.booking_date === booking.booking_date &&
                b.time_slot_id === booking.time_slot_id &&
                data.booking_players.some((p) => p.booking_id === b.id && p.user_id === me.id)
        );

        if (clash) {
            throw new MockHttpError(422, 'You already have a booking at that time.');
        }

        // FR-11 — joining updates the SAME booking. It never creates a second
        // reservation for the same court cell.
        data.booking_players.push({
            id: uuid(),
            booking_id: booking.id,
            user_id: me.id,
            player_position: 2,
            joined_at: nowIso(),
        });

        save();

        return expand(booking);
    },

    async myBookings(scope, identity) {
        await delay();

        const data = load();
        const me = actingUser(identity);
        const start = today();

        return data.bookings
            .filter((b) =>
                data.booking_players.some((p) => p.booking_id === b.id && p.user_id === me.id)
            )
            .filter((b) => {
                if (scope === 'upcoming') return b.status === 'CONFIRMED' && b.booking_date >= start;
                if (scope === 'cancelled') return b.status === 'CANCELLED';
                return b.booking_date < start || b.status === 'CANCELLED';
            })
            .map(expand)
            .sort((a, b) =>
                (a.booking_date + a.time_slot.start_time).localeCompare(
                    b.booking_date + b.time_slot.start_time
                )
            );
    },

    async cancelBooking(bookingId, identity) {
        await delay();

        const data = load();
        const me = actingUser(identity);
        const booking = data.bookings.find((b) => b.id === bookingId);

        if (!booking) {
            throw new MockHttpError(404, 'That booking no longer exists.');
        }

        if (booking.status === 'CANCELLED') {
            throw new MockHttpError(422, 'That booking is already cancelled.');
        }

        const players = playersFor(booking.id);

        if (!players.some((p) => p.user_id === me.id)) {
            // BR-10 — students manage only their own bookings.
            throw new MockHttpError(403, 'You can only cancel your own bookings.');
        }

        const slot = slotById(booking.time_slot_id);

        if (withinCancellationCutoff(booking.booking_date, slot)) {
            throw new MockHttpError(
                422,
                `Bookings cannot be cancelled within ${PHASE1_RULES.cancellationCutoffHours} hours of the start time.`
            );
        }

        if (booking.created_by_user_id !== me.id && booking.booking_type === 'OPEN') {
            // Player 2 leaving frees the spot again; the booking itself stands.
            data.booking_players = data.booking_players.filter(
                (p) => !(p.booking_id === booking.id && p.user_id === me.id)
            );

            save();

            return { ...expand(booking), left: true };
        }

        // BR-19 — the reservation is released.
        booking.status = 'CANCELLED';
        booking.cancelled_at = nowIso();

        save();

        return expand(booking);
    },
};
