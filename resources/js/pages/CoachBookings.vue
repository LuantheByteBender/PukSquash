<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { http, query } from '../services/http.js';

function todayYmd() {
    const d = new Date();
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

function addDaysYmd(ymd, delta) {
    const [y, m, d] = String(ymd || '').split('-').map((n) => Number(n));
    if (!y || !m || !d) return todayYmd();

    const dt = new Date(y, m - 1, d);
    dt.setDate(dt.getDate() + delta);

    const yyyy = dt.getFullYear();
    const mm = String(dt.getMonth() + 1).padStart(2, '0');
    const dd = String(dt.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
}

const selectedDate = ref(todayYmd());

const timeSlots = ref([]);
const published = ref([]);
const selectedTimeSlotIds = ref([]);

const loadingSlots = ref(false);
const loadingAvailability = ref(false);
const publishing = ref(false);

const error = ref('');
const success = ref('');

const now = ref(new Date());
let clockTimer = null;

function startClock() {
    clockTimer = setInterval(() => { now.value = new Date(); }, 30_000);
}

function stopClock() {
    if (clockTimer) { clearInterval(clockTimer); clockTimer = null; }
}

const sortedTimeSlots = computed(() =>
    [...timeSlots.value].sort(
        (a, b) => (a?.display_order ?? 0) - (b?.display_order ?? 0)
    )
);

const isToday = computed(() => selectedDate.value === todayYmd());

const currentHHMMSS = computed(() => {
    const h = String(now.value.getHours()).padStart(2, '0');
    const m = String(now.value.getMinutes()).padStart(2, '0');
    const s = String(now.value.getSeconds()).padStart(2, '0');
    return `${h}:${m}:${s}`;
});

const pastSlotIds = computed(() => {
    if (!isToday.value) return new Set();
    const out = new Set();
    for (const slot of timeSlots.value) {
        const start = slot?.start_time || slot?.startTime || '';
        if (start && start <= currentHHMMSS.value) {
            out.add(slot.id);
        }
    }
    return out;
});

const publishedSlotIds = computed(() => {
    const out = new Set();
    for (const row of published.value || []) {
        const id = row?.time_slot?.id || row?.timeSlot?.id;
        if (id) out.add(id);
    }
    return out;
});

const bookedSlotIds = computed(() => {
    const out = new Set();
    for (const row of published.value || []) {
        if (row?.has_confirmed_booking) {
            const id = row?.time_slot?.id || row?.timeSlot?.id;
            if (id) out.add(id);
        }
    }
    return out;
});

const selectedSlotIdsSet = computed(() => new Set(selectedTimeSlotIds.value));
const selectedCount = computed(() => selectedTimeSlotIds.value.length);

const formattedDate = computed(() => {
    const [y, m, d] = (selectedDate.value || '').split('-').map(Number);
    if (!y || !m || !d) return selectedDate.value;

    const dt = new Date(y, m - 1, d);
    return dt.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
});

function slotLabel(slot) {
    const start = slot?.start_time || slot?.startTime || '';
    const end = slot?.end_time || slot?.endTime || '';

    if (start && end) return `${start} - ${end}`;
    return start || end || 'Time slot';
}

function availabilitySlotLabel(row) {
    const slot = row?.time_slot || row?.timeSlot;
    return slotLabel(slot);
}

function formatCreatedAt(value) {
    if (!value) return '—';
    const dt = new Date(value);
    if (Number.isNaN(dt.getTime())) return String(value);

    return dt.toLocaleString();
}

function goToday() {
    selectedDate.value = todayYmd();
}

function shiftDay(delta) {
    selectedDate.value = addDaysYmd(selectedDate.value, delta);
}

function isSlotDisabled(slotId) {
    return pastSlotIds.value.has(slotId)
        || publishedSlotIds.value.has(slotId)
        || bookedSlotIds.value.has(slotId);
}

function slotStatus(slotId) {
    if (pastSlotIds.value.has(slotId)) return 'past';
    if (bookedSlotIds.value.has(slotId)) return 'booked';
    if (publishedSlotIds.value.has(slotId)) return 'published';
    if (selectedSlotIdsSet.value.has(slotId)) return 'selected';
    return 'available';
}

function toggleSlot(slotId) {
    success.value = '';
    error.value = '';

    if (isSlotDisabled(slotId)) return;

    if (selectedSlotIdsSet.value.has(slotId)) {
        selectedTimeSlotIds.value = selectedTimeSlotIds.value.filter((id) => id !== slotId);
        return;
    }

    selectedTimeSlotIds.value = [...selectedTimeSlotIds.value, slotId];
}

async function loadTimeSlots() {
    loadingSlots.value = true;
    error.value = '';

    try {
        timeSlots.value = await http.get('/time-slots');
    } catch (e) {
        error.value = e?.message ?? String(e);
    } finally {
        loadingSlots.value = false;
    }
}

async function loadMyAvailability() {
    loadingAvailability.value = true;
    error.value = '';

    try {
        const res = await http.get(
            `/coach/availability${query({ availability_date: selectedDate.value })}`
        );

        published.value = res?.data || [];
    } catch (e) {
        published.value = [];
        error.value = e?.message ?? String(e);
    } finally {
        loadingAvailability.value = false;
    }
}

async function publishAvailability() {
    if (publishing.value) return;
    if (!selectedTimeSlotIds.value.length) return;

    publishing.value = true;
    error.value = '';
    success.value = '';

    try {
        await http.put('/coach/availability', {
            availability_date: selectedDate.value,
            time_slot_ids: selectedTimeSlotIds.value,
        });

        selectedTimeSlotIds.value = [];
        await loadMyAvailability();

        success.value = 'Availability published.';
        setTimeout(() => {
            success.value = '';
        }, 2500);
    } catch (e) {
        error.value = e?.message ?? String(e);
    } finally {
        publishing.value = false;
    }
}

async function removeAvailability(row) {
    const timeSlotId = row?.time_slot?.id || row?.timeSlot?.id;
    if (!timeSlotId) return;

    error.value = '';
    success.value = '';

    try {
        await http.delete('/coach/availability', {
            availability_date: selectedDate.value,
            time_slot_id: timeSlotId,
        });

        await loadMyAvailability();

        success.value = 'Availability removed.';
        setTimeout(() => {
            success.value = '';
        }, 2500);
    } catch (e) {
        error.value = e?.message ?? String(e);
    }
}

watch(selectedDate, async () => {
    selectedTimeSlotIds.value = [];
    await loadMyAvailability();
});

onMounted(async () => {
    startClock();
    await loadTimeSlots();
    await loadMyAvailability();
});

onUnmounted(() => {
    stopClock();
});
</script>

<template>
    <div class="ca-page">
        <div class="ca-hero">
            <div class="ca-hero__inner">
                <div class="ca-hero__text">
                    <h1 class="ca-hero__title">Coach Availability</h1>
                    <p class="ca-hero__sub">Manage when you're available for coaching sessions.</p>
                </div>

                <div class="ca-date-strip">
                    <div class="ca-date-pick">
                        <input v-model="selectedDate" type="date" class="ca-date-input" />
                    </div>
                </div>
            </div>
        </div>

        <main class="ca-body">
            <Transition name="ca-alert">
                <div v-if="error" class="ca-alert ca-alert--error" @click="error = ''">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" x2="9" y1="9" y2="15"/><line x1="9" x2="15" y1="9" y2="15"/></svg>
                    <span>{{ error }}</span>
                </div>
            </Transition>

            <Transition name="ca-alert">
                <div v-if="success" class="ca-alert ca-alert--success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                    <span>{{ success }}</span>
                </div>
            </Transition>

            <div class="ca-grid">
                <section class="ca-card">
                    <div class="ca-card__head">
                        <div>
                            <h2 class="ca-card__title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                Select time slots
                            </h2>
                            <p class="ca-card__sub">Choose one or more slots, then publish.</p>
                        </div>
                        <div class="ca-counter" :class="{ 'ca-counter--active': selectedCount > 0 }">
                            {{ selectedCount }}
                        </div>
                    </div>

                    <div class="ca-slots-wrap">
                        <div v-if="loadingSlots" class="ca-loader">
                            <span class="ca-spinner" />
                            <span>Loading slots...</span>
                        </div>

                        <div v-else-if="!sortedTimeSlots.length" class="ca-empty-mini">
                            No time slots configured.
                        </div>

                        <div v-else class="ca-slots-grid">
                            <button
                                v-for="slot in sortedTimeSlots"
                                :key="slot.id"
                                type="button"
                                :disabled="isSlotDisabled(slot.id)"
                                :aria-pressed="selectedSlotIdsSet.has(slot.id)"
                                class="ca-slot"
                                :class="{
                                    'ca-slot--past': slotStatus(slot.id) === 'past',
                                    'ca-slot--booked': slotStatus(slot.id) === 'booked',
                                    'ca-slot--published': slotStatus(slot.id) === 'published',
                                    'ca-slot--selected': slotStatus(slot.id) === 'selected',
                                }"
                                @click="toggleSlot(slot.id)"
                            >
                                <span class="ca-slot__time">{{ slotLabel(slot) }}</span>
                                <span v-if="slotStatus(slot.id) === 'selected'" class="ca-slot__check">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span v-if="slotStatus(slot.id) === 'past'" class="ca-slot__badge ca-slot__badge--past">Past</span>
                                <span v-else-if="slotStatus(slot.id) === 'booked'" class="ca-slot__badge ca-slot__badge--booked">Booked</span>
                                <span v-else-if="slotStatus(slot.id) === 'published'" class="ca-slot__badge">Live</span>
                            </button>
                        </div>
                    </div>

                    <div class="ca-card__foot">
                        <button
                            type="button"
                            :disabled="publishing || selectedTimeSlotIds.length === 0"
                            class="ca-btn ca-btn--primary"
                            @click="publishAvailability"
                        >
                            <svg v-if="!publishing" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5"/><path d="m5 12 7-7 7 7"/></svg>
                            <span class="ca-spinner ca-spinner--sm" v-if="publishing" />
                            {{ publishing ? 'Publishing...' : 'Publish' }}
                        </button>
                    </div>
                </section>

                <section class="ca-card">
                    <div class="ca-card__head">
                        <div>
                            <h2 class="ca-card__title">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                Published
                            </h2>
                            <p class="ca-card__sub">{{ formattedDate }}</p>
                        </div>
                        <button type="button" class="ca-btn ca-btn--ghost" @click="loadMyAvailability">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M21 21v-5h-5"/></svg>
                            Refresh
                        </button>
                    </div>

                    <div class="ca-published-wrap">
                        <div v-if="loadingAvailability" class="ca-loader">
                            <span class="ca-spinner" />
                            <span>Loading...</span>
                        </div>

                        <div v-else-if="published.length" class="ca-published-list">
                            <div
                                v-for="row in published"
                                :key="row.id"
                                class="ca-pub-row"
                                :class="{ 'ca-pub-row--booked': row.has_confirmed_booking }"
                            >
                                <div class="ca-pub-row__left">
                                    <span
                                        class="ca-pub-row__dot"
                                        :class="{ 'ca-pub-row__dot--booked': row.has_confirmed_booking }"
                                    />
                                    <div class="ca-pub-row__info">
                                        <p class="ca-pub-row__time">{{ availabilitySlotLabel(row) }}</p>
                                        <p class="ca-pub-row__meta">{{ formatCreatedAt(row.created_at || row.createdAt) }}</p>
                                    </div>
                                </div>
                                <span v-if="row.has_confirmed_booking" class="ca-pub-row__locked">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    Booked
                                </span>
                                <button
                                    v-else
                                    type="button"
                                    class="ca-btn ca-btn--danger-sm"
                                    @click="removeAvailability(row)"
                                >
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                    Remove
                                </button>
                            </div>
                        </div>

                        <div v-else class="ca-empty">
                            <div class="ca-empty__icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><line x1="10" x2="14" y1="15" y2="15"/></svg>
                            </div>
                            <h3 class="ca-empty__title">No slots published</h3>
                            <p class="ca-empty__text">Select time slots and publish to make yourself available.</p>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</template>

<style scoped>
.ca-page {
    min-height: 100vh;
    background: #f8f7fb;
}

.ca-hero {
    background: linear-gradient(135deg, #6C3D91 0%, #512d6e 50%, #3d2154 100%);
    padding: 2.5rem 1.5rem 3.5rem;
    position: relative;
    overflow: hidden;
}

.ca-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.04);
    pointer-events: none;
}

.ca-hero::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.03);
    pointer-events: none;
}

.ca-hero__inner {
    max-width: 64rem;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

@media (min-width: 640px) {
    .ca-hero__inner {
        flex-direction: row;
        align-items: flex-end;
        justify-content: space-between;
    }
}

.ca-hero__title {
    font-size: 1.75rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.025em;
    line-height: 1.2;
}

.ca-hero__sub {
    margin-top: 0.4rem;
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.7);
}

.ca-date-strip {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    align-items: stretch;
}

@media (min-width: 640px) {
    .ca-date-strip {
        flex-direction: row;
        align-items: center;
    }
}

.ca-date-input {
    height: 2.25rem;
    padding: 0 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    font-size: 0.8125rem;
    font-weight: 600;
    backdrop-filter: blur(4px);
    outline: none;
    color-scheme: dark;
    cursor: pointer;
    transition: background 180ms ease, border-color 180ms ease;
}

.ca-date-input:focus {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(255, 255, 255, 0.4);
}

.ca-body {
    max-width: 64rem;
    margin: -2rem auto 0;
    padding: 0 1.5rem 3rem;
    position: relative;
    z-index: 1;
}

.ca-alert {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.8125rem;
    font-weight: 600;
    margin-bottom: 1rem;
    cursor: pointer;
}

.ca-alert--error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.ca-alert--success {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.ca-alert-enter-active,
.ca-alert-leave-active {
    transition: all 300ms ease;
}

.ca-alert-enter-from,
.ca-alert-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

.ca-grid {
    display: grid;
    gap: 1.25rem;
}

@media (min-width: 1024px) {
    .ca-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.ca-card {
    background: #fff;
    border-radius: 1rem;
    border: 1px solid #eee;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 4px 12px rgba(108, 61, 145, 0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.ca-card__head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f3f0f7;
}

.ca-card__title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 1rem;
    font-weight: 700;
    color: #1f1f1f;
}

.ca-card__title svg {
    color: #6C3D91;
    flex-shrink: 0;
}

.ca-card__sub {
    margin-top: 0.2rem;
    font-size: 0.8125rem;
    color: #888;
}

.ca-card__foot {
    padding: 1rem 1.5rem;
    border-top: 1px solid #f3f0f7;
    display: flex;
    justify-content: flex-end;
}

.ca-counter {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    font-size: 0.875rem;
    font-weight: 800;
    background: #f3f0f7;
    color: #999;
    transition: all 250ms ease;
    flex-shrink: 0;
}

.ca-counter--active {
    background: #6C3D91;
    color: #fff;
    box-shadow: 0 2px 8px rgba(108, 61, 145, 0.3);
}

.ca-slots-wrap {
    padding: 1.25rem 1.5rem;
    flex: 1;
}

.ca-slots-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}

@media (min-width: 640px) {
    .ca-slots-grid {
        grid-template-columns: 1fr 1fr 1fr;
    }
}

.ca-slot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.4rem;
    padding: 0.625rem 0.75rem;
    border-radius: 0.625rem;
    border: 1.5px solid #e5e1eb;
    background: #fff;
    font: inherit;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #333;
    cursor: pointer;
    transition: all 180ms ease;
    outline: none;
}

.ca-slot:hover:not(:disabled) {
    border-color: #c4b5d4;
    background: #faf8fc;
}

.ca-slot:focus-visible {
    box-shadow: 0 0 0 3px rgba(108, 61, 145, 0.2);
}

.ca-slot--selected {
    border-color: #6C3D91;
    background: linear-gradient(135deg, #f5eefb, #ece4f5);
    color: #4a2666;
}

.ca-slot--selected:hover:not(:disabled) {
    background: linear-gradient(135deg, #ece4f5, #e2d6f0);
}

.ca-slot--published {
    border-color: #e8e8e8;
    background: #fafafa;
    color: #aaa;
    cursor: not-allowed;
}

.ca-slot--past {
    border-color: #e8e8e8;
    background: repeating-linear-gradient(
        -45deg,
        #fafafa,
        #fafafa 4px,
        #f3f3f3 4px,
        #f3f3f3 8px
    );
    color: #bbb;
    cursor: not-allowed;
    opacity: 0.7;
}

.ca-slot--booked {
    border-color: #fde68a;
    background: #fffbeb;
    color: #92400e;
    cursor: not-allowed;
}

.ca-slot__time {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ca-slot__check {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 50%;
    background: #6C3D91;
    color: #fff;
    flex-shrink: 0;
}

.ca-slot__badge {
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.125rem 0.4rem;
    border-radius: 999px;
    background: #e8e8e8;
    color: #999;
    flex-shrink: 0;
}

.ca-slot__badge--past {
    background: #f0f0f0;
    color: #bbb;
}

.ca-slot__badge--booked {
    background: #fef3c7;
    color: #92400e;
}

.ca-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.6rem 1.25rem;
    border-radius: 0.6rem;
    font: inherit;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
    border: none;
    transition: all 180ms ease;
    outline: none;
}

.ca-btn:focus-visible {
    box-shadow: 0 0 0 3px rgba(108, 61, 145, 0.2);
}

.ca-btn--primary {
    background: linear-gradient(135deg, #6C3D91, #5b347a);
    color: #fff;
    box-shadow: 0 2px 8px rgba(108, 61, 145, 0.25);
}

.ca-btn--primary:hover:not(:disabled) {
    box-shadow: 0 4px 14px rgba(108, 61, 145, 0.35);
    transform: translateY(-1px);
}

.ca-btn--primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.ca-btn--ghost {
    background: transparent;
    color: #6C3D91;
    border: 1px solid #e5e1eb;
    padding: 0.4rem 0.85rem;
    font-size: 0.75rem;
}

.ca-btn--ghost:hover {
    background: #f5eefb;
    border-color: #c4b5d4;
}

.ca-btn--danger-sm {
    background: transparent;
    color: #dc2626;
    border: 1px solid #fecaca;
    padding: 0.35rem 0.75rem;
    font-size: 0.75rem;
}

.ca-btn--danger-sm:hover {
    background: #fef2f2;
    border-color: #fca5a5;
}

.ca-published-wrap {
    padding: 1.25rem 1.5rem;
    flex: 1;
}

.ca-published-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ca-pub-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1px solid #f0ecf4;
    background: #fcfaff;
    transition: all 180ms ease;
}

.ca-pub-row:hover {
    border-color: #e5e1eb;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}

.ca-pub-row__left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.ca-pub-row__dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 50%;
    background: #6C3D91;
    flex-shrink: 0;
    box-shadow: 0 0 0 3px rgba(108, 61, 145, 0.15);
}

.ca-pub-row__dot--booked {
    background: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15);
}

.ca-pub-row--booked {
    border-color: #fde68a;
    background: #fffbeb;
}

.ca-pub-row__locked {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #92400e;
    padding: 0.35rem 0.75rem;
    border-radius: 0.5rem;
    background: #fef3c7;
    border: 1px solid #fde68a;
    flex-shrink: 0;
}

.ca-pub-row__info {
    min-width: 0;
}

.ca-pub-row__time {
    font-size: 0.875rem;
    font-weight: 700;
    color: #1f1f1f;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ca-pub-row__meta {
    font-size: 0.6875rem;
    color: #999;
    margin-top: 0.15rem;
}

.ca-loader {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    padding: 2.5rem 1rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #999;
}

.ca-spinner {
    width: 1.125rem;
    height: 1.125rem;
    border: 2.5px solid #e5e1eb;
    border-top-color: #6C3D91;
    border-radius: 50%;
    animation: ca-spin 0.7s linear infinite;
    flex-shrink: 0;
}

.ca-spinner--sm {
    width: 0.875rem;
    height: 0.875rem;
    border-width: 2px;
    border-color: rgba(255, 255, 255, 0.3);
    border-top-color: #fff;
}

@keyframes ca-spin {
    to { transform: rotate(360deg); }
}

.ca-empty {
    text-align: center;
    padding: 2.5rem 1rem;
}

.ca-empty__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 1rem;
    background: linear-gradient(135deg, #f5eefb, #ece4f5);
    color: #6C3D91;
    margin-bottom: 0.75rem;
}

.ca-empty__title {
    font-size: 0.9375rem;
    font-weight: 700;
    color: #333;
}

.ca-empty__text {
    font-size: 0.8125rem;
    color: #999;
    margin-top: 0.3rem;
    max-width: 14rem;
    margin-left: auto;
    margin-right: auto;
}

.ca-empty-mini {
    text-align: center;
    padding: 2rem 1rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #999;
}
</style>