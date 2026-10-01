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
    selectedKeys: { type: Array, default: null },
    /** "courtId:slotId" of a cell currently being submitted. */
    busyKey: { type: String, default: null },
    /** Array of time_slot_id values that have one or more coaches available for the selected date. */
    coachSlotIds: { type: Array, default: null },
});

const emit = defineEmits(['select']);

const keyFor = (courtId, slotId) => `${courtId}:${slotId}`;

const selectedKeySet = computed(() => new Set(props.selectedKeys || []));

const coachSlotIdSet = computed(() => new Set((props.coachSlotIds || []).map(String)));

function hasCoach(slotId) {
    return coachSlotIdSet.value.has(String(slotId));
}

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

/**
 * The backend sends every cell that cannot be booked as UNAVAILABLE and tells
 * the reasons apart only by label: "Inactive" (court switched off), "—" (the slot
 * has already started) and "Unavailable" (you already hold a court at this hour).
 * These get their own look so a student does not read "you are booked on
 * another court" as "every court is gone". Presentation only — the backend still
 * decides what can be booked.
 */
function visualState(cell) {
    if (cell.state !== 'UNAVAILABLE') return cell.state.toLowerCase();
    if (cell.label === 'Unavailable') return 'busy';
    if (cell.label === '—') return 'past';

    return 'unavailable';
}

const VISUAL_LABELS = {
    busy: 'Booked elsewhere',
    past: 'Past',
};

const displayLabel = (cell) => VISUAL_LABELS[visualState(cell)] || cell.label;

const hhmm = (time) => (time || '').slice(0, 5);

const STATE_DESCRIPTIONS = {
    AVAILABLE: 'available',
    OPEN: 'open booking with one player, you may join',
    FULL: 'full',
    LOCKED: 'locked private booking',
    PRIVATE_COACH: 'private coaching session',
    UNAVAILABLE: 'unavailable',
};

const VISUAL_DESCRIPTIONS = {
    busy: 'unavailable, you already have a booking at this time',
    past: 'unavailable, this time has passed',
};

function describe(court, slot, cell) {
    const when = `${hhmm(slot.start_time)} to ${hhmm(slot.end_time)}`;
    const what =
        VISUAL_DESCRIPTIONS[visualState(cell)] || STATE_DESCRIPTIONS[cell.state] || cell.state;

    const coach = hasCoach(slot.id) && cell.can_book ? ', coach available' : '';

    return `${court.name}, ${when}, ${what}${coach}`;
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
                    </th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="slot in timeSlots" :key="slot.id">
                    <th scope="row" class="time">
                        {{ hhmm(slot.start_time) }}<span class="dash">–</span>{{ hhmm(slot.end_time) }}

                        <span v-if="hasCoach(slot.id)" class="coach-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                        </span>
                    </th>

                    <td v-for="court in courts" :key="court.id" class="cell-td">
                        <component
                            :is="isActionable(cellAt(court.id, slot.id)) ? 'button' : 'div'"
                            class="cell"
                            :class="[
                                `state-${visualState(cellAt(court.id, slot.id))}`,
                                {
                                    mine: cellAt(court.id, slot.id).is_mine,
                                    selected: selectedKeySet.has(keyFor(court.id, slot.id)),
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
                                <span class="cell-label">{{ displayLabel(cellAt(court.id, slot.id)) }}</span>

                                <span v-if="cellAt(court.id, slot.id).can_join" class="tag tag-join">Join</span>
                            </template>
                        </component>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
/* Frozen panes. The grid scrolls inside its own box in both directions, so the
   court-name header row stays pinned to the top and the Time column stays pinned
   to the left however far you scroll. (With only sideways scrolling here, the
   header row's `position: sticky` had nothing to stick to and scrolled away with
   the page.) The "Time" corner is frozen in both directions so the two never
   come apart. Every frozen cell needs an opaque background — a transparent one
   lets the scrolling court columns show through it. */
.grid-wrap {
    overflow: auto;
    max-height: min(70vh, 640px);
    overscroll-behavior: contain;
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
    table-layout: fixed;
    min-width: 640px;
}

thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: var(--ps-surface);
    border-bottom: 1px solid var(--ps-border);
    box-shadow: 0 1px 0 var(--ps-border);
    padding: 0.55rem 0.4rem;
    font-size: 0.8rem;
    font-weight: 650;
    color: var(--ps-text);
    text-align: center;
}

.court-head,
.cell-td {
    width: 150px;
    min-width: 150px;
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

.coach-icon {
    display: inline-flex;
    align-items: center;
    margin-left: 0.25rem;
    color: var(--ps-brand);
}

.cell-td {
    padding: 3px;
    border-bottom: 1px solid var(--ps-border-soft);
}

.cell {
    /* border-box so read-only cells (rendered as <div>) are the same width as
       bookable ones (<button>, which is border-box by default) and do not spill
       into the neighbouring column. */
    box-sizing: border-box;
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
    word-break: break-word;
    transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.clickable {
    cursor: pointer;
}

.clickable:hover:not(:disabled) {
    filter: brightness(0.95);
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.clickable:active:not(:disabled) {
    transform: translateY(0) scale(0.97);
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

.state-busy {
    background: var(--ps-busy);
    border-color: var(--ps-busy-bd);
    color: var(--ps-busy-ink);
}

.state-past {
    background: var(--ps-past);
    border-color: var(--ps-past-bd);
    color: var(--ps-past-ink);
}

/* The approved wireframe marks the student's own booking with a bold dark
   outline rather than a colour, which stays legible on every cell state. */
.mine {
    box-shadow: inset 0 0 0 2px var(--ps-text);
}

.selected {
    box-shadow: inset 0 0 0 2px var(--ps-brand);
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

    .court-head,
    .cell-td {
        width: 118px;
        min-width: 118px;
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
