<script setup>
import { computed } from 'vue';

const props = defineProps({
    booking: { type: Object, required: true },
    cancelling: { type: Boolean, default: false },
    showCancel: { type: Boolean, default: false },
});

defineEmits(['cancel']);

const hhmm = (time) => (time || '').slice(0, 5);

const TYPE_LABELS = {
    OPEN: 'Open session',
    LOCKED: 'Locked session',
    PRIVATE_COACH: 'Private coaching',
};

const TYPE_CLASSES = {
    OPEN: 'pill pill-blue',
    LOCKED: 'pill pill-grey',
    PRIVATE_COACH: 'pill pill-amber',
};

const typeLabel = computed(
    () => TYPE_LABELS[props.booking.booking_type] || props.booking.booking_type
);

const typeClass = computed(() => TYPE_CLASSES[props.booking.booking_type] || 'pill');

const when = computed(() =>
    new Date(`${props.booking.booking_date}T00:00:00`).toLocaleDateString(undefined, {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
    })
);

const opponent = computed(() => {
    if (props.booking.booking_type !== 'OPEN') return null;

    const players = props.booking.players || [];

    if (players.length < 2) return 'Waiting for a second player';

    const sorted = [...players].sort(
        (a, b) => (a.player_position ?? 99) - (b.player_position ?? 99)
    );

    const a = sorted[0];
    const b = sorted[1];

    const nameA = a?.name || a?.full_name || a?.fullName || 'Player 1';
    const nameB = b?.name || b?.full_name || b?.fullName || 'Player 2';

    return `${nameA} vs ${nameB}`;
});
</script>

<template>
    <div class="booking" :class="{ cancelled: booking.status === 'CANCELLED' }">
        <div class="when">
            <span class="date">{{ when }}</span>
            <span class="time">
                {{ hhmm(booking.time_slot?.start_time) }}–{{ hhmm(booking.time_slot?.end_time) }}
            </span>
        </div>

        <div class="body">
            <div class="row">
                <strong>{{ booking.court?.name }}</strong>
                <span :class="typeClass">{{ typeLabel }}</span>
                <span v-if="booking.status === 'CANCELLED'" class="pill pill-red">Cancelled</span>
            </div>

            <p v-if="booking.coach" class="sub">Coach: {{ booking.coach.name }}</p>
            <p v-else-if="booking.player" class="sub">Player: {{ booking.player.name }}</p>
            <p v-else-if="opponent" class="sub">{{ opponent }}</p>
        </div>

        <button
            v-if="showCancel && booking.status === 'CONFIRMED'"
            class="btn btn-secondary btn-sm"
            :disabled="cancelling"
            @click="$emit('cancel', booking)"
        >
            {{ cancelling ? 'Cancelling…' : 'Cancel' }}
        </button>
    </div>
</template>

<style scoped>
.booking {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1.1rem;
    background: var(--ps-surface);
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.booking:hover {
    box-shadow: var(--ps-shadow);
    transform: translateY(-2px);
    border-color: rgba(108, 62, 148, 0.15);
}

.booking.cancelled {
    opacity: 0.55;
}

.when {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 84px;
    padding: 0.4rem 0.6rem;
    background: linear-gradient(135deg, rgba(242, 236, 247, 0.8) 0%, rgba(242, 236, 247, 1) 100%);
    border-radius: var(--ps-radius-sm);
    border: 1px solid rgba(221, 207, 233, 0.4);
}

.date {
    font-size: 0.72rem;
    color: var(--ps-brand);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.time {
    font-size: 0.85rem;
    font-weight: 650;
    color: var(--ps-text);
}

.body {
    flex: 1;
    min-width: 0;
}

.row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.row strong {
    font-weight: 700;
}

.sub {
    font-size: 0.8rem;
    margin: 0.2rem 0 0;
    color: var(--ps-text-muted);
}

@media (max-width: 520px) {
    .booking {
        flex-wrap: wrap;
    }

    .body {
        flex-basis: 60%;
    }
}
</style>
