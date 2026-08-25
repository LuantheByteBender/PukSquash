<script setup>
/** FR-15 / FR-16 — view your bookings and cancel your own. */
import { ref, onMounted } from 'vue';
import { bookingService } from '../features/booking/bookingService.js';
import { useAuth } from '../composables/useAuth.js';
import { useToast } from '../composables/useToast.js';
import BookingCard from '../components/BookingCard.vue';
import LoadingSpinner from '../components/LoadingSpinner.vue';

const { user } = useAuth();
const toast = useToast();

const tabs = [
    { key: 'upcoming', label: 'Upcoming' },
    { key: 'past', label: 'Past' },
    { key: 'cancelled', label: 'Cancelled' },
];

const tab = ref('upcoming');
const bookings = ref([]);
const loading = ref(true);
const error = ref(null);
const cancellingId = ref(null);
const confirming = ref(null);

async function load() {
    loading.value = true;
    error.value = null;

    try {
        bookings.value = await bookingService.myBookings(tab.value, user.value);
    } catch (err) {
        error.value = err.message;
    } finally {
        loading.value = false;
    }
}

onMounted(load);

function switchTab(key) {
    tab.value = key;
    load();
}

async function doCancel() {
    const booking = confirming.value;
    confirming.value = null;
    cancellingId.value = booking.id;

    try {
        const result = await bookingService.cancelBooking(booking.id, user.value);

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
            <p class="muted">Everything you have booked or joined.</p>
        </div>

        <div class="tabs" role="tablist">
            <button
                v-for="item in tabs"
                :key="item.key"
                class="tab"
                :class="{ on: tab === item.key }"
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

        <div v-else-if="!bookings.length" class="card centred empty">
            <p v-if="tab === 'upcoming'">You have no upcoming bookings.</p>
            <p v-else-if="tab === 'past'">Nothing here yet.</p>
            <p v-else>You have not cancelled any bookings.</p>

            <RouterLink v-if="tab === 'upcoming'" :to="{ name: 'book' }" class="btn">
                Book a court
            </RouterLink>
        </div>

        <div v-else class="list">
            <BookingCard
                v-for="booking in bookings"
                :key="booking.id"
                :booking="booking"
                :show-cancel="tab === 'upcoming'"
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
    margin-bottom: 1rem;
}

.page-head h1 {
    margin: 0 0 0.15rem;
}

.page-head p {
    margin: 0;
}

.tabs {
    display: flex;
    gap: 0.25rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--ps-border);
}

.tab {
    border: 0;
    background: none;
    cursor: pointer;
    padding: 0.5rem 0.8rem;
    font: inherit;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--ps-text-muted);
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
}

.tab.on {
    color: var(--ps-brand);
    border-bottom-color: var(--ps-brand);
}

.list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.centred {
    display: grid;
    place-items: center;
    padding: 2rem 1rem;
    text-align: center;
}

.empty .btn {
    margin-top: 0.6rem;
    text-decoration: none;
}

.backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    padding: 1rem;
    background: rgba(24, 14, 33, 0.5);
    display: grid;
    place-items: center;
}

.dialog {
    max-width: 420px;
    width: 100%;
    background: var(--ps-surface);
    border-radius: var(--ps-radius);
    padding: 1.1rem;
    box-shadow: 0 10px 30px rgba(24, 14, 33, 0.25);
}

.dialog h2 {
    margin: 0 0 0.5rem;
    font-size: 1.15rem;
}

.actions {
    display: flex;
    gap: 0.6rem;
    justify-content: flex-end;
    margin-top: 1rem;
}
</style>
