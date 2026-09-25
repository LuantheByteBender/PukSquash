<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import AvailabilityGrid from '../components/AvailabilityGrid.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import GridLegend from '../components/GridLegend.vue';
import { bookingService, ApiConflict } from '../features/booking/bookingService.js';
import { useToast } from '../composables/useToast.js';

const toast = useToast();

function isoDateLocal(value) {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, '0');
    const day = String(value.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function addDaysIso(ymd, delta) {
    const dt = new Date(`${ymd}T00:00:00`);
    dt.setDate(dt.getDate() + delta);
    return isoDateLocal(dt);
}

function datesBetween(startIso, endIso) {
    if (!startIso || !endIso) return [];
    if (endIso < startIso) return [startIso];

    const out = [];
    let current = startIso;

    while (current <= endIso) {
        out.push(current);
        current = addDaysIso(current, 1);
    }

    return out;
}

function hhmm(time) {
    return (time || '').slice(0, 5);
}

const todayIso = isoDateLocal(new Date());

const startDate = ref(todayIso);
const endDate = ref(todayIso);

const availability = ref({});
const loading = ref(false);
const loadError = ref('');

const selections = ref(new Map());

const confirmOpen = ref(false);
const submitting = ref(false);

const lastBookedIds = ref([]);

const bookingWindowDays = computed(() => {
    const first = availability.value[startDate.value];
    return first?.booking_window_days ?? 14;
});

const maxDate = computed(() => addDaysIso(todayIso, bookingWindowDays.value));

const selectedDates = computed(() => datesBetween(startDate.value, endDate.value));

const selectedKeysByDate = computed(() => {
    const map = {};

    for (const date of selectedDates.value) {
        map[date] = [];
    }

    for (const [key, row] of selections.value.entries()) {
        const date = row.booking_date;
        if (!map[date]) map[date] = [];
        map[date].push(`${row.court_id}:${row.time_slot_id}`);
    }

    return map;
});

const selectionList = computed(() => {
    const rows = Array.from(selections.value.values());

    rows.sort((a, b) => {
        if (a.booking_date !== b.booking_date) return a.booking_date.localeCompare(b.booking_date);
        if (a.start_time !== b.start_time) return String(a.start_time).localeCompare(String(b.start_time));
        return String(a.court_name).localeCompare(String(b.court_name));
    });

    return rows;
});

const selectionCount = computed(() => selectionList.value.length);

const confirmMessage = computed(() => {
    if (selectionCount.value === 1) {
        return 'Confirm this team practice reservation?';
    }

    return `Confirm these ${selectionCount.value} team practice reservations?`;
});

async function loadAvailability() {
    const dates = selectedDates.value;

    if (!dates.length) {
        availability.value = {};
        return;
    }

    loading.value = true;
    loadError.value = '';

    try {
        const results = await Promise.all(
            dates.map(async (date) => ({
                date,
                data: await bookingService.coachTeamPracticeAvailability(date),
            }))
        );

        const next = {};
        for (const item of results) {
            next[item.date] = item.data;
        }

        availability.value = next;
    } catch (error) {
        loadError.value = error?.message ?? String(error);
        availability.value = {};
    } finally {
        loading.value = false;
    }
}

function onConfirmDialogConfirm() {
    if (submitting.value) return;
    confirmOpen.value = false;
    confirmBooking();
}

function toggleSelection(date, court, slot, cell) {
    if (!cell?.can_book) return;

    const key = `${date}:${court.id}:${slot.id}`;

    if (selections.value.has(key)) {
        selections.value.delete(key);
        selections.value = new Map(selections.value);
        return;
    }

    selections.value.set(key, {
        booking_date: date,
        court_id: court.id,
        court_name: court.name,
        time_slot_id: slot.id,
        start_time: slot.start_time,
        end_time: slot.end_time,
    });

    selections.value = new Map(selections.value);
}

function removeSelection(row) {
    const key = `${row.booking_date}:${row.court_id}:${row.time_slot_id}`;
    selections.value.delete(key);
    selections.value = new Map(selections.value);
}

function clearSelections() {
    selections.value = new Map();
}

async function confirmBooking() {
    if (submitting.value) return;

    const payload = selectionList.value.map((row) => ({
        booking_date: row.booking_date,
        court_id: row.court_id,
        time_slot_id: row.time_slot_id,
    }));

    submitting.value = true;

    try {
        const res = await bookingService.createTeamPractice(payload);
        lastBookedIds.value = res?.data?.booking_ids ?? [];
        clearSelections();
        toast.success('Team practice booked successfully.');
    } catch (error) {
        if (error instanceof ApiConflict) {
            toast.warn(error.message);
        } else {
            toast.error(error?.message ?? String(error));
        }
    } finally {
        submitting.value = false;
        await loadAvailability();
    }
}

watch(
    () => startDate.value,
    (next) => {
        if (next < todayIso) startDate.value = todayIso;
        if (next > maxDate.value) startDate.value = maxDate.value;
        if (endDate.value < startDate.value) endDate.value = startDate.value;
    }
);

watch(
    () => endDate.value,
    (next) => {
        if (next < startDate.value) endDate.value = startDate.value;
        if (next > maxDate.value) endDate.value = maxDate.value;
    }
);

watch([startDate, endDate], async () => {
    await loadAvailability();
});

onMounted(loadAvailability);
</script>

<template>
    <div class="page">
        <div class="page-head">
            <div>
                <h1>Team practice booking</h1>
                <p class="muted">
                    Select one date or a consecutive date range, then choose multiple courts and time slots.
                </p>
            </div>
        </div>

        <div class="card controls">
            <div class="row">
                <div class="field">
                    <label class="muted" for="tp-start">Start date</label>
                    <input id="tp-start" v-model="startDate" type="date" :min="todayIso" :max="maxDate" />
                </div>

                <div class="field">
                    <label class="muted" for="tp-end">End date</label>
                    <input id="tp-end" v-model="endDate" type="date" :min="startDate" :max="maxDate" />
                </div>

                <div class="spacer" />

                <div class="counter">
                    <span class="pill pill-blue">Selected: {{ selectionCount }}</span>
                </div>

                <button class="btn btn-secondary" type="button" :disabled="selectionCount === 0" @click="clearSelections">
                    Clear
                </button>

                <button class="btn" type="button" :disabled="selectionCount === 0 || submitting" @click="confirmOpen = true">
                    Review & confirm
                </button>
            </div>
        </div>

        <p v-if="loadError" class="alert alert-error">{{ loadError }}</p>

        <!-- One spinner only (bug #10): the app-wide overlay shows while the dates
             load, instead of an extra spinner here and one per date card. -->
        <template v-if="loading && !Object.keys(availability).length"></template>

        <div v-else>
            <GridLegend show-selected class="tp-legend" />

            <div v-for="date in selectedDates" :key="date" class="card grid-card">
                <div class="grid-head">
                    <h2>{{ new Date(`${date}T00:00:00`).toLocaleDateString(undefined, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}</h2>
                </div>

                <p v-if="!availability[date]?.courts?.length || !availability[date]?.time_slots?.length" class="muted empty">
                    No courts are open for booking on this date.
                </p>

                <AvailabilityGrid
                    v-else
                    :courts="availability[date].courts"
                    :time-slots="availability[date].time_slots"
                    :cells="availability[date].cells"
                    :selected-keys="selectedKeysByDate[date]"
                    @select="({ court, slot, cell }) => toggleSelection(date, court, slot, cell)"
                />
            </div>
        </div>

        <div class="card review" v-if="selectionCount">
            <div class="review-head">
                <h2>Selected reservations</h2>
                <span class="pill">{{ selectionCount }} total</span>
            </div>

            <div class="review-list">
                <div v-for="row in selectionList" :key="`${row.booking_date}:${row.court_id}:${row.time_slot_id}`" class="review-row">
                    <div class="review-meta">
                        <div class="review-main">
                            <span class="review-date">{{ row.booking_date }}</span>
                            <span class="review-court">{{ row.court_name }}</span>
                        </div>
                        <div class="review-sub muted">
                            {{ hhmm(row.start_time) }}–{{ hhmm(row.end_time) }}
                        </div>
                    </div>

                    <button class="btn btn-secondary btn-sm" type="button" @click="removeSelection(row)">
                        Remove
                    </button>
                </div>
            </div>
        </div>

        <div class="card" v-if="lastBookedIds.length">
            <h2>Latest team practice booking</h2>
            <p class="muted">Created {{ lastBookedIds.length }} bookings.</p>
        </div>

        <ConfirmDialog
            :open="confirmOpen"
            title="Confirm team practice"
            :message="confirmMessage"
            confirm-text="Confirm"
            cancel-text="Cancel"
            :confirm-variant="'primary'"
            @cancel="confirmOpen = false"
            @confirm="onConfirmDialogConfirm"
        />
    </div>
</template>

<style scoped>
.page-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}

.page-head h1 {
    margin: 0 0 0.15rem;
}

.controls .row {
    gap: 0.8rem;
}

.field {
    display: grid;
    gap: 0.25rem;
    min-width: 180px;
}

.counter {
    display: flex;
    align-items: center;
}

.centred {
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
}

.tp-legend {
    margin-top: 1rem;
}

.grid-card {
    margin-top: 1rem;
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

.empty {
    margin: 0;
    padding: 1.2rem 0;
    text-align: center;
}

.review {
    margin-top: 1rem;
}

.review-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.review-head h2 {
    margin: 0;
    font-size: 1rem;
}

.review-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.review-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.6rem 0.7rem;
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius-sm);
    background: #fff;
}

.review-main {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
    font-weight: 650;
}

.review-date {
    font-variant-numeric: tabular-nums;
}

.review-sub {
    font-size: 0.85rem;
}

.spacer {
    flex: 1;
}
</style>
