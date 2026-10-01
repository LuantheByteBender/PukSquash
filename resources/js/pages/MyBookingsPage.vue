<script setup>
/** FR-15 / FR-16 — view your bookings and cancel your own. */
import { ref, computed, onMounted } from 'vue';
import { bookingService } from '../features/booking/bookingService.js';
import { useToast } from '../composables/useToast.js';
import { useAccount } from '../composables/useAccount.js';
import BookingCard from '../components/BookingCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';

const toast = useToast();

const tabs = [
    { key: 'upcoming', label: 'Upcoming' },
    { key: 'past', label: 'Past' },
    { key: 'cancelled', label: 'Cancelled' },
];

const tab = ref('upcoming');
const view = ref('mine');
const bookings = ref([]);
const loading = ref(true);
const error = ref(null);
const cancellingId = ref(null);
const confirming = ref(null);
const searchQuery = ref('');
const typeFilter = ref('');

const { role } = useAccount();

const filteredBookings = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    const type = typeFilter.value;

    return bookings.value.filter((b) => {
        const matchesType = !type || b.booking_type === type;
        const matchesQuery =
            !query ||
            (b.court?.name || '').toLowerCase().includes(query) ||
            (b.booking_date || '').includes(query) ||
            (b.coach?.name || '').toLowerCase().includes(query) ||
            (b.time_slot?.start_time || '').includes(query);

        return matchesType && matchesQuery;
    });
});

async function load() {
    loading.value = true;
    error.value = null;

    try {
        if (view.value === 'coach') {
            const coachBookings = await bookingService.myCoachBookingsUpcoming();
            bookings.value = (coachBookings || []).map((b) => ({ ...b, booking_type: 'PRIVATE_COACH' }));
        } else {
            bookings.value = await bookingService.myBookings(tab.value);
        }
    } catch (err) {
        error.value = err.message;
    } finally {
        loading.value = false;
    }
}

onMounted(load);

function switchTab(key) {
    if (view.value === 'coach' && key !== 'upcoming') {
        toast.info('Coaching sessions are only available for Upcoming.');
        return;
    }
    tab.value = key;
    load();
}

function switchView(key) {
    if (key === view.value) return;
    view.value = key;

    if (view.value === 'coach') {
        tab.value = 'upcoming';
    }

    load();
}

async function doCancel() {
    const booking = confirming.value;
    confirming.value = null;
    cancellingId.value = booking.id;

    try {
        const result = await bookingService.cancelBooking(booking.id);

        toast.success(
            result.left
                ? 'You have left that session. The other player keeps the court.'
                : 'Booking cancelled. The slot is available again.'
        );

        await load();
    } catch (err) {
        toast.error(err.message);
    } finally {
        cancellingId.value = null;
    }
}

const hhmm = (time) => (time || '').slice(0, 5);
</script>

<template>
    <div class="page">
        <div class="page-head">
            <h1>My bookings</h1>
            <p v-if="view === 'coach'" class="muted">Upcoming coaching sessions booked with you.</p>
            <p v-else class="muted">Everything you have booked or joined.</p>
        </div>

        <div v-if="role === 'COACH'" class="tabs" role="tablist">
            <button
                class="tab"
                :class="{ on: view === 'mine' }"
                role="tab"
                :aria-selected="view === 'mine'"
                @click="switchView('mine')"
            >
                My bookings
            </button>

            <button
                class="tab"
                :class="{ on: view === 'coach' }"
                role="tab"
                :aria-selected="view === 'coach'"
                @click="switchView('coach')"
            >
                Coach bookings
            </button>
        </div>

        <div class="tabs" role="tablist">
            <button
                v-for="item in tabs"
                :key="item.key"
                class="tab"
                :class="{ on: tab === item.key }"
                :disabled="view === 'coach' && item.key !== 'upcoming'"
                role="tab"
                :aria-selected="tab === item.key"
                @click="switchTab(item.key)"
            >
                {{ item.label }}
            </button>
        </div>

        <div v-if="loading" class="card centred">
            <LoadingSpinner />
        </div>

        <p v-else-if="error" class="alert alert-error">{{ error }}</p>

        <div class="filter-bar">
            <label class="filter-field filter-field--grow">
                <span class="filter-label">Search</span>
                <input v-model="searchQuery" type="text" placeholder="Court, date, coach…" />
            </label>
            <label class="filter-field">
                <span class="filter-label">Type</span>
                <select v-model="typeFilter">
                    <option value="">All types</option>
                    <option value="OPEN">Open session</option>
                    <option value="LOCKED">Locked session</option>
                    <option value="PRIVATE_COACH">Private coaching</option>
                </select>
            </label>
        </div>

        <div v-if="!loading && !error && !filteredBookings.length && bookings.length" class="card centred empty">
            <p>No bookings match your filter.</p>
        </div>

        <div v-else-if="!bookings.length && !loading && !error" class="card centred empty">
            <p v-if="view === 'coach'">You have no upcoming coaching sessions.</p>
            <p v-else-if="tab === 'upcoming'">You have no upcoming bookings.</p>
            <p v-else-if="tab === 'past'">Nothing here yet.</p>
            <p v-else>You have not cancelled any bookings.</p>

            <RouterLink v-if="view === 'mine' && tab === 'upcoming'" :to="{ name: 'book' }" class="btn">
                Book a court
            </RouterLink>
        </div>

        <div v-else-if="!loading && !error" class="list list--scrollable">
            <BookingCard
                v-for="booking in filteredBookings"
                :key="booking.id"
                :booking="booking"
                :show-cancel="view === 'mine' && tab === 'upcoming'"
                :cancelling="cancellingId === booking.id"
                @cancel="confirming = $event"
            />
        </div>

        <div v-if="confirming" class="backdrop" @click.self="confirming = null">
            <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="cancel-title">
                <h2 id="cancel-title">Cancel this booking?</h2>

                <p class="muted">
                    {{ confirming.court?.name }},
                    {{ hhmm(confirming.time_slot?.start_time) }}–{{ hhmm(confirming.time_slot?.end_time) }}
                    on {{ confirming.booking_date }}. The slot goes back into availability for other
                    students.
                </p>

                <div class="actions">
                    <button class="btn btn-secondary" @click="confirming = null">Keep booking</button>
                    <button class="btn btn-danger" @click="doCancel">Cancel booking</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-head {
    margin-bottom: 1.25rem;
}

.page-head h1 {
    margin: 0 0 0.2rem;
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -0.02em;
}

.page-head p {
    margin: 0;
}

.tabs {
    display: flex;
    gap: 0.2rem;
    margin-bottom: 1.25rem;
    border-bottom: 1px solid var(--ps-border-soft);
}

.tab {
    border: 0;
    background: none;
    cursor: pointer;
    padding: 0.55rem 0.9rem;
    font: inherit;
    font-weight: 600;
    font-size: 0.85rem;
    color: var(--ps-text-muted);
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 6px 6px 0 0;
}

.tab:hover:not(:disabled) {
    color: var(--ps-brand-light);
    background: rgba(108, 62, 148, 0.04);
}

.tab.on {
    color: var(--ps-brand);
    border-bottom-color: var(--ps-brand);
}

.tab:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.filter-bar {
    display: flex;
    gap: 0.6rem;
    flex-wrap: wrap;
    align-items: flex-end;
    margin-bottom: 1rem;
}

.filter-field {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    min-width: 160px;
}

.filter-field--grow {
    flex: 1;
    min-width: 200px;
}

.filter-label {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--ps-text-faint);
}

.list {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.list--scrollable {
    max-height: 520px;
    overflow-y: auto;
    padding-right: 0.25rem;
}

.centred {
    display: grid;
    place-items: center;
    padding: 2.5rem 1rem;
    text-align: center;
}

.empty .btn {
    margin-top: 0.75rem;
    text-decoration: none;
}

.backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    padding: 1rem;
    background: rgba(23, 18, 29, 0.55);
    backdrop-filter: blur(4px);
    display: grid;
    place-items: center;
    animation: my-fade 0.2s ease;
}

.dialog {
    max-width: 440px;
    width: 100%;
    background: var(--ps-surface-elevated, #fff);
    border-radius: var(--ps-radius-lg, 16px);
    padding: 1.5rem;
    box-shadow: var(--ps-shadow-xl);
    animation: my-scale 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.dialog h2 {
    margin: 0 0 0.4rem;
    font-size: 1.1rem;
    font-weight: 700;
}

.actions {
    display: flex;
    gap: 0.6rem;
    justify-content: flex-end;
    margin-top: 1.25rem;
}

@keyframes my-fade {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes my-scale {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}
</style>
