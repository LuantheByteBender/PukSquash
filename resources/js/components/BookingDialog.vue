<script setup>
/**
 * Booking confirmation dialog.
 *
 * Shows exactly what is being reserved before anything is committed, and requires
 * an explicit session type. For a private coaching session it loads only the
 * coaches published as available for this exact date and slot (FR-09, BR-11) —
 * it never lists every coach and filters in the browser.
 */
import { ref, watch, computed } from 'vue';
import { bookingService } from '../features/booking/bookingService.js';
import LoadingSpinner from './LoadingSpinner.vue';

const props = defineProps({
    court: { type: Object, required: true },
    timeSlot: { type: Object, required: true },
    date: { type: String, required: true },
    cell: { type: Object, required: true },
    submitting: { type: Boolean, default: false },
    /** A rejection the user can act on here (a booking limit, say). Conflicts are
     *  NOT shown here — those close the dialog and refresh the grid. */
    error: { type: String, default: null },
});

const emit = defineEmits(['close', 'confirm', 'join']);

const joining = computed(() => props.cell.state === 'OPEN');

const type = ref('OPEN');
const coachId = ref(null);
const coaches = ref([]);
const loadingCoaches = ref(false);
const coachError = ref(null);

const hhmm = (time) => (time || '').slice(0, 5);

const when = computed(
    () =>
        `${formatDate(props.date)}, ${hhmm(props.timeSlot.start_time)}–${hhmm(props.timeSlot.end_time)}`
);

function formatDate(iso) {
    return new Date(`${iso}T00:00:00`).toLocaleDateString(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    });
}

async function loadCoaches() {
    loadingCoaches.value = true;
    coachError.value = null;
    coaches.value = [];
    coachId.value = null;

    try {
        coaches.value = await bookingService.availableCoaches(props.date, props.timeSlot.id);

        if (coaches.value.length === 1) {
            coachId.value = coaches.value[0].id;
        }
    } catch (error) {
        coachError.value = error.message;
    } finally {
        loadingCoaches.value = false;
    }
}

watch(type, (next) => {
    if (next === 'PRIVATE_COACH') {
        loadCoaches();
    }
});

const canConfirm = computed(() => {
    if (props.submitting) return false;
    if (type.value === 'PRIVATE_COACH') return Boolean(coachId.value);

    return true;
});

function confirm() {
    emit('confirm', {
        court_id: props.court.id,
        time_slot_id: props.timeSlot.id,
        booking_date: props.date,
        booking_type: type.value,
        coach_id: type.value === 'PRIVATE_COACH' ? coachId.value : null,
    });
}

const sessionTypes = [
    {
        value: 'OPEN',
        title: 'Open session',
        blurb: 'Another student may join you. Good if you still need an opponent.',
    },
    {
        value: 'LOCKED',
        title: 'Locked session',
        blurb: 'The court is yours. Nobody else can join this booking.',
    },
    {
        value: 'PRIVATE_COACH',
        title: 'Private coaching',
        blurb: 'Book the court together with an available coach.',
    },
];
</script>

<template>
    <div class="backdrop" @click.self="emit('close')">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="booking-dialog-title">
            <div class="head">
                <h2 id="booking-dialog-title">
                    {{ joining ? 'Join this session' : 'Confirm your booking' }}
                </h2>

                <button class="close" aria-label="Close" @click="emit('close')">&times;</button>
            </div>

            <dl class="summary">
                <div>
                    <dt>Court</dt>
                    <dd>{{ court.name }}</dd>
                </div>
                <div>
                    <dt>When</dt>
                    <dd>{{ when }}</dd>
                </div>
            </dl>

            <!-- Joining an existing OPEN booking (FR-10) -->
            <template v-if="joining">
                <p class="muted">
                    {{ cell.players?.[0]?.name || 'Another student' }} has this court booked and is
                    looking for an opponent. Joining adds you as the second player on the same
                    booking — it does not reserve another court.
                </p>

                <p v-if="error" class="alert alert-error">{{ error }}</p>

                <div class="actions">
                    <button class="btn btn-secondary" @click="emit('close')">Cancel</button>
                    <button
                        class="btn"
                        :disabled="submitting"
                        @click="emit('join', cell.booking_id)"
                    >
                        {{ submitting ? 'Joining…' : 'Join session' }}
                    </button>
                </div>
            </template>

            <!-- Creating a new booking (FR-07 / FR-08 / FR-09) -->
            <template v-else>
                <fieldset class="types">
                    <legend>Session type</legend>

                    <label
                        v-for="option in sessionTypes"
                        :key="option.value"
                        class="type"
                        :class="{ on: type === option.value }"
                    >
                        <input v-model="type" type="radio" :value="option.value" name="session-type" />

                        <span class="type-body">
                            <span class="type-title">{{ option.title }}</span>
                            <span class="type-blurb">{{ option.blurb }}</span>
                        </span>
                    </label>
                </fieldset>

                <div v-if="type === 'PRIVATE_COACH'" class="coach">
                    <label for="coach-select">Available coach</label>

                    <LoadingSpinner v-if="loadingCoaches" label="Checking who is available…" />

                    <p v-else-if="coachError" class="alert alert-error">{{ coachError }}</p>

                    <p v-else-if="!coaches.length" class="alert alert-warn">
                        No coach has published availability for this time. Try another slot.
                    </p>

                    <select v-else id="coach-select" v-model="coachId">
                        <option :value="null" disabled>Select a coach…</option>
                        <option v-for="coach in coaches" :key="coach.id" :value="coach.id">
                            {{ coach.name }}
                        </option>
                    </select>
                </div>

                <p v-if="error" class="alert alert-error">{{ error }}</p>

                <div class="actions">
                    <button class="btn btn-secondary" @click="emit('close')">Cancel</button>
                    <button class="btn" :disabled="!canConfirm" @click="confirm">
                        {{ submitting ? 'Booking…' : 'Confirm booking' }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<style scoped>
.backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(23, 18, 29, 0.55);
    backdrop-filter: blur(4px);
    display: grid;
    place-items: center;
    padding: 1rem;
    animation: bd-fade 0.2s ease;
}

.dialog {
    width: 100%;
    max-width: 460px;
    max-height: 90vh;
    overflow-y: auto;
    background: var(--ps-surface-elevated, #fff);
    border-radius: var(--ps-radius-lg, 16px);
    box-shadow: var(--ps-shadow-xl);
    padding: 1.5rem;
    animation: bd-scale 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.6rem;
}

.head h2 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
}

.close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 0;
    background: transparent;
    font-size: 1.3rem;
    line-height: 1;
    cursor: pointer;
    color: var(--ps-text-muted);
    border-radius: 8px;
    transition: all var(--ps-transition-fast);
    padding: 0;
}

.close:hover {
    color: var(--ps-text);
    background: var(--ps-brand-tint);
}

.summary {
    margin: 1rem 0;
    padding: 0.75rem 0.9rem;
    background: linear-gradient(135deg, rgba(242, 236, 247, 0.6) 0%, rgba(242, 236, 247, 0.9) 100%);
    border: 1px solid rgba(221, 207, 233, 0.5);
    border-radius: var(--ps-radius);
    display: grid;
    gap: 0.35rem;
}

.summary div {
    display: flex;
    gap: 0.6rem;
}

.summary dt {
    width: 54px;
    flex: none;
    color: var(--ps-text-muted);
    font-size: 0.82rem;
    font-weight: 500;
}

.summary dd {
    margin: 0;
    font-weight: 650;
    font-size: 0.875rem;
}

.types {
    border: 0;
    padding: 0;
    margin: 0 0 1rem;
}

.types legend {
    font-weight: 700;
    font-size: 0.85rem;
    padding: 0;
    margin-bottom: 0.5rem;
}

.type {
    display: flex;
    gap: 0.65rem;
    align-items: flex-start;
    padding: 0.65rem 0.8rem;
    border: 1.5px solid var(--ps-border-soft);
    border-radius: var(--ps-radius);
    cursor: pointer;
    margin-bottom: 0.45rem;
    transition: all var(--ps-transition-fast);
}

.type:hover {
    border-color: var(--ps-brand-light);
    background: rgba(242, 236, 247, 0.4);
}

.type.on {
    border-color: var(--ps-brand);
    background: var(--ps-brand-tint);
    box-shadow: 0 0 0 1px var(--ps-brand) inset;
}

.type input {
    margin-top: 0.2rem;
    accent-color: var(--ps-brand);
}

.type-title {
    display: block;
    font-weight: 700;
    font-size: 0.875rem;
}

.type-blurb {
    display: block;
    color: var(--ps-text-muted);
    font-size: 0.8rem;
    margin-top: 0.1rem;
    line-height: 1.4;
}

.coach {
    margin-bottom: 1rem;
}

.coach label {
    display: block;
    font-weight: 700;
    font-size: 0.85rem;
    margin-bottom: 0.3rem;
}

.actions {
    display: flex;
    gap: 0.6rem;
    justify-content: flex-end;
    margin-top: 0.75rem;
}

@keyframes bd-fade {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes bd-scale {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}
</style>
