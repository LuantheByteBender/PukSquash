<script setup>
/* Colour alone must never carry the meaning of a cell, so every cell also shows
   text. This legend explains what those states mean the first time someone
   looks at the grid. */

const items = [
    ['available', 'Available', 'Free — you can book it'],
    ['open', 'Open', 'One player booked, you may join'],
    ['full', 'Full', 'Two players — closed'],
    ['locked', 'Locked', 'Private booking, cannot be joined'],
    ['private_coach', 'Private coach', 'Coaching session'],
    ['unavailable', 'Unavailable', 'Court inactive or slot disabled'],
];
</script>

<template>
    <ul class="legend">
        <li v-for="[cls, label, hint] in items" :key="cls">
            <span class="swatch" :class="`state-${cls}`" aria-hidden="true"></span>
            <span class="label">{{ label }}</span>
            <span class="hint">{{ hint }}</span>
        </li>
    </ul>
</template>

<style scoped>
.legend {
    list-style: none;
    margin: 0.75rem 0 0;
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
    width: 14px;
    height: 14px;
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

.state-unavailable {
    background: var(--ps-inactive);
    border-color: var(--ps-inactive-bd);
}
</style>
