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
    padding: 0.8rem 1rem;
    background: var(--ps-surface);
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius);
}

.booking.cancelled {
    opacity: 0.65;
}

.when {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-width: 84px;
    padding: 0.35rem 0.5rem;
    background: var(--ps-brand-tint);
    border-radius: var(--ps-radius-sm);
}

.date {
    font-size: 0.75rem;
    color: var(--ps-brand);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}

.time {
    font-size: 0.85rem;
    font-weight: 600;
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

.sub {
    font-size: 0.83rem;
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
