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
import { bookingService, ApiConflict } from '../features/booking/bookingService.js';
import { useToast } from '../composables/useToast.js';
import AvailabilityGrid from '../components/AvailabilityGrid.vue';
import GridLegend from '../components/GridLegend.vue';
import BookingDialog from '../components/BookingDialog.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';

const toast = useToast();

function isoDateLocal(value) {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

const todayIso = isoDateLocal(new Date());

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

    return isoDateLocal(value);
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
        data.value = await bookingService.availability(date.value);
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

    const iso = isoDateLocal(value);

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

    const selected = selection.value;
    const when = selected?.slot
        ? `${(selected.slot.start_time || '').slice(0, 5)}–${(selected.slot.end_time || '').slice(0, 5)}`
        : '';

    try {
        await bookingService.createBooking(payload);
        selection.value = null;

        if (selected?.court?.name && when) {
            toast.success(`Booked — ${selected.court.name}, ${when}.`);
        } else {
            toast.success('Booked successfully.');
        }
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

    const selected = selection.value;
    const host = selected?.cell?.players?.[0]?.name;

    try {
        await bookingService.joinBooking(bookingId);
        selection.value = null;

        if (host && selected?.court?.name) {
            toast.success(`You have joined ${host}'s session on ${selected.court.name}.`);
        } else {
            toast.success('You have joined the booking.');
        }
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

/** An empty grid is a real state, not a failure: the admin has configured no
 *  active courts or no active time slots for this date (BR-04, BR-05). */
const gridEmpty = computed(
    () => !data.value?.courts?.length || !data.value?.time_slots?.length
);
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

                <p v-if="gridEmpty" class="muted empty">
                    No courts are open for booking on this date.
                </p>

                <template v-else>
                    <!-- Legend sits above the grid, and the grid scrolls inside its
                         own box, so the key stays on screen while you scan the slots. -->
                    <GridLegend class="legend-above" />

                    <AvailabilityGrid
                        :courts="data.courts"
                        :time-slots="data.time_slots"
                        :cells="data.cells"
                        :busy-key="busyKey"
                        @select="onSelect"
                    />
                </template>
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

.legend-above {
    margin-bottom: 0.75rem;
}

.empty {
    margin: 0;
    padding: 1.5rem 0;
    text-align: center;
}
</style>
