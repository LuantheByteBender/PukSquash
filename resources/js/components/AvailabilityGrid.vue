<script setup>
/**
 * The court availability grid.
 *
 * Rows are fixed one-hour time slots, columns are courts. Each cell is one
 * court + date + time slot combination (§8 of the detailed requirements).
 *
 * This component renders state. It never derives it. Every cell arrives from the
 * backend already carrying its state, its label and whether the current user may
 * act on it. If this component ever starts working availability out from raw
 * booking data, the two implementations will drift, and the drift will look
 * exactly like a booking bug.
 */
import { computed } from 'vue';

const props = defineProps({
    courts: { type: Array, required: true },
    timeSlots: { type: Array, required: true },
    cells: { type: Array, required: true },
    /** "courtId:slotId" of a cell currently being submitted. */
    busyKey: { type: String, default: null },
});

const emit = defineEmits(['select']);

const keyFor = (courtId, slotId) => `${courtId}:${slotId}`;

const index = computed(() => {
    const map = new Map();

    for (const cell of props.cells) {
        map.set(keyFor(cell.court_id, cell.time_slot_id), cell);
    }

    return map;
});

function cellAt(courtId, slotId) {
    return (
        index.value.get(keyFor(courtId, slotId)) || {
            state: 'UNAVAILABLE',
            label: '—',
            can_book: false,
            can_join: false,
        }
    );
}

const isActionable = (cell) => Boolean(cell.can_book || cell.can_join);

const hhmm = (time) => (time || '').slice(0, 5);

const STATE_DESCRIPTIONS = {
    AVAILABLE: 'available',
    OPEN: 'open booking with one player, you may join',
    FULL: 'full',
    LOCKED: 'locked private booking',
    PRIVATE_COACH: 'private coaching session',
    UNAVAILABLE: 'unavailable',
};

function describe(court, slot, cell) {
    const when = `${hhmm(slot.start_time)} to ${hhmm(slot.end_time)}`;
    const what = STATE_DESCRIPTIONS[cell.state] || cell.state;

    return `${court.name}, ${when}, ${what}`;
}
</script>

<template>
    <div class="grid-wrap">
        <table class="grid">
            <caption class="sr-only">
                Court availability. Rows are one-hour time slots, columns are courts.
            </caption>

            <thead>
                <tr>
                    <th scope="col" class="time-head">Time</th>

                    <th v-for="court in courts" :key="court.id" scope="col" class="court-head">
                        <span>{{ court.name }}</span>
                        <span v-if="court.status !== 'ACTIVE'" class="pill pill-grey">Inactive</span>
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="slot in timeSlots" :key="slot.id">
                    <th scope="row" class="time">
                        {{ hhmm(slot.start_time) }}<span class="dash">–</span>{{ hhmm(slot.end_time) }}
                    </th>

                    <td v-for="court in courts" :key="court.id" class="cell-td">
                        <component
                            :is="isActionable(cellAt(court.id, slot.id)) ? 'button' : 'div'"
                            class="cell"
                            :class="[
                                `state-${cellAt(court.id, slot.id).state.toLowerCase()}`,
                                {
                                    mine: cellAt(court.id, slot.id).is_mine,
                                    clickable: isActionable(cellAt(court.id, slot.id)),
                                },
                            ]"
                            :type="isActionable(cellAt(court.id, slot.id)) ? 'button' : undefined"
                            :disabled="
                                isActionable(cellAt(court.id, slot.id))
                                    ? busyKey === keyFor(court.id, slot.id)
                                    : undefined
                            "
                            :aria-label="describe(court, slot, cellAt(court.id, slot.id))"
                            @click="
                                isActionable(cellAt(court.id, slot.id)) &&
                                    emit('select', {
                                        court,
                                        slot,
                                        cell: cellAt(court.id, slot.id),
                                    })
                            "
                        >
                            <span
                                v-if="busyKey === keyFor(court.id, slot.id)"
                                class="dot-spinner"
                                aria-hidden="true"
                            ></span>

                            <template v-else>
                                <span class="cell-label">{{ cellAt(court.id, slot.id).label }}</span>

                                <span v-if="cellAt(court.id, slot.id).is_mine" class="tag tag-you">You</span>
                                <span v-else-if="cellAt(court.id, slot.id).can_join" class="tag tag-join">Join</span>
                            </template>
                        </component>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
/* The grid scrolls sideways on a phone, and the Time column is the only thing
   that says which row you are looking at. It is frozen to the left edge, and the
   "Time" header corner is frozen in both directions so the two never come apart.
   Every frozen cell needs an opaque background — a transparent one lets the
   scrolling court columns show through it. */
.grid-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius);
    background: var(--ps-surface);
}

.grid {
    width: 100%;
    /* separate, not collapse: collapsed borders are painted by the table rather
       than the cell, and they do not travel with a sticky column. */
    border-collapse: separate;
    border-spacing: 0;
    min-width: 640px;
}

thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: var(--ps-surface);
    border-bottom: 1px solid var(--ps-border);
    padding: 0.55rem 0.4rem;
    font-size: 0.8rem;
    font-weight: 650;
    color: var(--ps-text);
    text-align: center;
}

.court-head .pill {
    display: block;
    margin-top: 0.15rem;
}

/* Frozen column — width fixed so the courts do not shift under it as it scrolls. */
.time-head,
.time {
    position: sticky;
    left: 0;
    width: 92px;
    min-width: 92px;
    background: var(--ps-surface);
    /* The divider is a shadow, not a border: it stays on the frozen edge and
       does not add to the column's width. */
    box-shadow: 1px 0 0 var(--ps-border-soft);
}

.time-head {
    /* Above both the frozen column and the sticky header row — it overlaps both. */
    z-index: 4;
    text-align: left;
    padding-left: 0.7rem;
}

.time {
    z-index: 1;
    white-space: nowrap;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ps-text-muted);
    padding: 0.3rem 0.7rem;
    border-bottom: 1px solid var(--ps-border-soft);
    text-align: left;
}

.dash {
    opacity: 0.5;
    margin: 0 1px;
}

.cell-td {
    padding: 3px;
    border-bottom: 1px solid var(--ps-border-soft);
}

.cell {
    width: 100%;
    min-height: 46px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2px;
    padding: 0.3rem 0.35rem;
    border-radius: var(--ps-radius-sm);
    border: 1px solid;
    font: inherit;
    font-size: 0.78rem;
    line-height: 1.15;
    text-align: center;
}

.clickable {
    cursor: pointer;
    transition: transform 0.08s ease, filter 0.12s ease;
}

.clickable:hover:not(:disabled) {
    filter: brightness(0.97);
    transform: translateY(-1px);
}

.clickable:disabled {
    cursor: wait;
}

.cell-label {
    font-weight: 600;
}

.state-available {
    background: var(--ps-available);
    border-color: var(--ps-available-bd);
    color: var(--ps-available-ink);
}

.state-open {
    background: var(--ps-open);
    border-color: var(--ps-open-bd);
    color: var(--ps-open-ink);
}

.state-full {
    background: var(--ps-full);
    border-color: var(--ps-full-bd);
    color: var(--ps-full-ink);
}

.state-locked {
    background: var(--ps-locked);
    border-color: var(--ps-locked-bd);
    color: var(--ps-locked-ink);
}

.state-private_coach {
    background: var(--ps-coach);
    border-color: var(--ps-coach-bd);
    color: var(--ps-coach-ink);
}

.state-unavailable {
    background: var(--ps-inactive);
    border-color: var(--ps-inactive-bd);
    color: var(--ps-inactive-ink);
}

/* The approved wireframe marks the student's own booking with a bold dark
   outline rather than a colour, which stays legible on every cell state. */
.mine {
    box-shadow: inset 0 0 0 2px var(--ps-text);
}

.tag {
    font-size: 0.62rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0 0.3rem;
    border-radius: 999px;
}

.tag-you {
    background: var(--ps-text);
    color: #fff;
}

.tag-join {
    background: rgba(17, 75, 134, 0.12);
}

.dot-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid var(--ps-border);
    border-top-color: var(--ps-brand);
    border-radius: 50%;
    animation: ps-spin 0.7s linear infinite;
}

@keyframes ps-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 600px) {
    .cell {
        min-height: 42px;
        font-size: 0.72rem;
    }

    /* Narrower frozen column so more courts fit beside it, and a soft shadow so
       it reads as sitting above the columns sliding underneath. */
    .time-head,
    .time {
        width: 68px;
        min-width: 68px;
        box-shadow: 2px 0 6px rgba(24, 14, 33, 0.1);
    }

    .time-head {
        padding-left: 0.5rem;
    }

    /* Stack start over end so the frozen column stays narrow. The dash becomes
       the line break; left visible it would read as a minus sign. */
    .time {
        font-size: 0.72rem;
        padding: 0.3rem 0.5rem;
        white-space: normal;
        line-height: 1.2;
    }

    .time .dash {
        display: block;
        margin: 0;
        height: 0;
        overflow: hidden;
    }
}
</style>
