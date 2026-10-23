<script setup>
/** Small inline loading indicator used while a request is in flight. */
defineProps({
    label: { type: String, default: 'Loading…' },
    /** Hide the text and show only the spinner. */
    quiet: { type: Boolean, default: false },
});
</script>

<template>
    <span class="loading" role="status">
        <span class="spinner" aria-hidden="true"></span>
        <span :class="quiet ? 'sr-only' : 'text'">{{ label }}</span>
    </span>
</template>

<style scoped>
.loading {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--ps-text-muted);
    font-size: 0.9rem;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2px solid var(--ps-border);
    border-top-color: var(--ps-brand);
    border-radius: 50%;
    animation: ps-spin 0.7s linear infinite;
    flex: none;
}

@keyframes ps-spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
