<script setup>
/* Colour alone must never carry the meaning of a cell, so every cell also shows
   text. This legend explains what those states mean the first time someone
   looks at the grid. It lists every look a cell can have, including the two
   outline markers ("your booking" and, on multi-select pages, "selected"). */

const props = defineProps({
    /** Show the "Selected" marker — only pages that let you pick several cells
     *  (team practice) use it. */
    showSelected: { type: Boolean, default: false },
});

const states = [
    ['available', 'Available', 'Free — you can book it'],
    ['open', 'Open', 'One player booked, you may join'],
    ['full', 'Full', 'Two players — closed'],
    ['locked', 'Locked', 'Private booking, cannot be joined'],
    ['private_coach', 'Private coach', 'Coaching session'],
    ['busy', 'Booked elsewhere', 'You already have a court at this time'],
    ['past', 'Past', 'This time slot has already started'],
    ['unavailable', 'Inactive', 'Court inactive or slot disabled'],
];
</script>

<template>
    <div class="legend-wrap">
        <p class="legend-title">Legend</p>

        <ul class="legend">
            <li v-for="[cls, label, hint] in states" :key="cls">
                <span class="swatch" :class="`state-${cls}`" aria-hidden="true"></span>
                <span class="label">{{ label }}</span>
                <span class="hint">{{ hint }}</span>
            </li>

            <li>
                <span class="swatch marker-mine" aria-hidden="true"></span>
                <span class="label">Your booking</span>
                <span class="hint">Dark outline</span>
            </li>

            <li v-if="props.showSelected">
                <span class="swatch marker-selected" aria-hidden="true"></span>
                <span class="label">Selected</span>
                <span class="hint">Purple outline — not booked yet</span>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.legend-wrap {
    padding: 0.6rem 0.75rem;
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius-sm);
    background: var(--ps-bg);
}

.legend-title {
    margin: 0 0 0.35rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--ps-text-muted);
}

.legend {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 0.35rem 0.9rem;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    font-size: 0.8rem;
}

.legend li {
    display: flex;
    align-items: center;
    gap: 0.45rem;
}

.swatch {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1px solid;
    flex: none;
}

.label {
    font-weight: 600;
}

.hint {
    color: var(--ps-text-muted);
}

.state-available {
    background: var(--ps-available);
    border-color: var(--ps-available-bd);
}

.state-open {
    background: var(--ps-open);
    border-color: var(--ps-open-bd);
}

.state-full {
    background: var(--ps-full);
    border-color: var(--ps-full-bd);
}

.state-locked {
    background: var(--ps-locked);
    border-color: var(--ps-locked-bd);
}

.state-private_coach {
    background: var(--ps-coach);
    border-color: var(--ps-coach-bd);
}

.state-busy {
    background: var(--ps-busy);
    border-color: var(--ps-busy-bd);
}

.state-past {
    background: var(--ps-past);
    border-color: var(--ps-past-bd);
}

.state-unavailable {
    background: var(--ps-inactive);
    border-color: var(--ps-inactive-bd);
}

/* Markers copy the outlines the grid draws, on a neutral swatch. */
.marker-mine {
    background: var(--ps-surface);
    border-color: transparent;
    box-shadow: inset 0 0 0 2px var(--ps-text);
}

.marker-selected {
    background: var(--ps-surface);
    border-color: transparent;
    box-shadow: inset 0 0 0 2px var(--ps-brand);
}
</style>
