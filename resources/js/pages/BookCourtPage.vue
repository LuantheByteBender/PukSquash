<script setup>
/**
 * Book a court — the operational heart of Phase 1.
 *
 * The rule this page exists to respect: seeing a cell as AVAILABLE does not
 * reserve it (§8.2). So after every create, join or failure we re-fetch the whole
 * grid from the backend rather than patching the cell in place. It costs one round
 * trip, and it is the difference between a grid that is right and a grid that
 * merely looks right.
 */
import { ref, computed, onMounted } from 'vue';
import { bookingService, ApiConflict, usingMockBookings } from '../features/booking/bookingService.js';
import { armConflict, conflictArmed, resetMockData } from '../features/booking/mockBookingData.js';
import { useAuth } from '../composables/useAuth.js';
import { useToast } from '../composables/useToast.js';
import AvailabilityGrid from '../components/AvailabilityGrid.vue';
import GridLegend from '../components/GridLegend.vue';
import BookingDialog from '../components/BookingDialog.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';

const { user } = useAuth();
const toast = useToast();

const todayIso = new Date().toISOString().slice(0, 10);

const date = ref(todayIso);
const data = ref(null);
const loading = ref(false);
const loadError = ref(null);

const selection = ref(null); // { court, slot, cell }
const submitting = ref(false);
const busyKey = ref(null);
const dialogError = ref(null);

const maxDate = computed(() => {
    const days = data.value?.booking_window_days ?? 14;
    const value = new Date(`${todayIso}T00:00:00`);
    value.setDate(value.getDate() + days);

    return value.toISOString().slice(0, 10);
});

const prettyDate = computed(() =>
    new Date(`${date.value}T00:00:00`).toLocaleDateString(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    })
);

async function load() {
    loading.value = true;
    loadError.value = null;

    try {
        data.value = await bookingService.availability(date.value, user.value);
    } catch (error) {
        loadError.value = error.message;
    } finally {
        loading.value = false;
    }
}

onMounted(load);

function shiftDay(days) {
    const value = new Date(`${date.value}T00:00:00`);
    value.setDate(value.getDate() + days);

    const iso = value.toISOString().slice(0, 10);

    if (iso < todayIso || iso > maxDate.value) return;

    date.value = iso;
    load();
}

function onSelect(next) {
    dialogError.value = null;
    selection.value = next;
}

async function createBooking(payload) {
    submitting.value = true;
    dialogError.value = null;
    busyKey.value = `${payload.court_id}:${payload.time_slot_id}`;

    try {
        const booking = await bookingService.createBooking(payload, user.value);
        selection.value = null;
        toast.success(`Booked — ${booking.court.name}, ${slotLabel(booking)}.`);
    } catch (error) {
        handleFailure(error);
    } finally {
        submitting.value = false;
        busyKey.value = null;
        await load(); // always resync, on success and on failure
    }
}

async function joinBooking(bookingId) {
    submitting.value = true;
    dialogError.value = null;
    busyKey.value = selection.value
        ? `${selection.value.court.id}:${selection.value.slot.id}`
        : null;

    try {
        const booking = await bookingService.joinBooking(bookingId, user.value);
        selection.value = null;
        toast.success(
            `You have joined ${booking.players[0]?.name}'s session on ${booking.court.name}.`
        );
    } catch (error) {
        handleFailure(error);
    } finally {
        submitting.value = false;
        busyKey.value = null;
        await load();
    }
}

function handleFailure(error) {
    if (error instanceof ApiConflict) {
        // Someone else won the race. Close the dialog and let the refreshed grid
        // speak for itself — this is the system working, not an application error.
        selection.value = null;
        toast.warn(error.message);
        return;
    }

    // Anything else (a booking limit, an invalid date, a lost connection) may be
    // something the user can act on, so keep the dialog open and put the reason
    // where they are already looking.
    dialogError.value = error.message;
    toast.error(error.message);
}

const slotLabel = (booking) =>
    `${(booking.time_slot.start_time || '').slice(0, 5)}–${(booking.time_slot.end_time || '').slice(0, 5)}`;

/* --- demo controls, mock data source only --------------------------------- */

const armed = ref(usingMockBookings ? conflictArmed() : false);

function toggleConflict() {
    armed.value = !armed.value;
    armConflict(armed.value);

    if (armed.value) {
        toast.info('The next booking attempt will be rejected as a conflict.');
    }
}

function resetDemo() {
    resetMockData();
    armed.value = false;
    load();
    toast.info('Demo data reset.');
}
</script>

<template>
    <div class="page">
        <div class="page-head">
            <div>
                <h1>Book a court</h1>
                <p class="muted">Pick a date, then choose an open slot.</p>
            </div>

            <div class="datebar">
                <button
                    class="btn btn-secondary btn-sm"
                    :disabled="date <= todayIso"
                    aria-label="Previous day"
                    @click="shiftDay(-1)"
                >
                    ‹
                </button>

                <input v-model="date" type="date" :min="todayIso" :max="maxDate" @change="load" />

                <button
                    class="btn btn-secondary btn-sm"
                    :disabled="date >= maxDate"
                    aria-label="Next day"
                    @click="shiftDay(1)"
                >
                    ›
                </button>
            </div>
        </div>

        <div v-if="loading && !data" class="card centred">
            <LoadingSpinner label="Checking availability…" />
        </div>

        <p v-else-if="loadError" class="alert alert-error">{{ loadError }}</p>

        <template v-else-if="data">
            <div class="card">
                <div class="grid-head">
                    <h2>{{ prettyDate }}</h2>
                    <LoadingSpinner v-if="loading" label="refreshing" />
                </div>

                <AvailabilityGrid
                    :courts="data.courts"
                    :time-slots="data.time_slots"
                    :cells="data.cells"
                    :busy-key="busyKey"
                    @select="onSelect"
                />

                <GridLegend />
            </div>

            <div v-if="usingMockBookings" class="card dev">
                <h3>Demo controls</h3>

                <p class="faint">
                    Present only while the booking screens run on mock data. Use these to show the
                    booking-conflict path in a sprint demo without opening two browsers.
                </p>

                <div class="row">
                    <button class="btn btn-secondary btn-sm" @click="toggleConflict">
                        {{ armed ? 'Disarm conflict' : 'Arm a booking conflict' }}
                    </button>

                    <button class="btn btn-secondary btn-sm" @click="resetDemo">
                        Reset demo data
                    </button>
                </div>
            </div>
        </template>

        <BookingDialog
            v-if="selection"
            :court="selection.court"
            :time-slot="selection.slot"
            :cell="selection.cell"
            :date="date"
            :submitting="submitting"
            :error="dialogError"
            @close="selection = null"
            @confirm="createBooking"
            @join="joinBooking"
        />
    </div>
</template>

<style scoped>
.page-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}

.page-head h1 {
    margin: 0 0 0.15rem;
}

.page-head p {
    margin: 0;
}

.datebar {
    display: flex;
    align-items: center;
    gap: 0.35rem;
}

.datebar input {
    width: auto;
    min-width: 150px;
}

.grid-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
    margin-bottom: 0.75rem;
}

.grid-head h2 {
    margin: 0;
    font-size: 1rem;
}

.centred {
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
}

.dev {
    border-style: dashed;
    margin-top: 1rem;
}

.dev h3 {
    margin: 0 0 0.2rem;
    font-size: 0.95rem;
}
</style>
