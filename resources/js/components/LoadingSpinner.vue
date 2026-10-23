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
    gap: 0.6rem;
    color: var(--ps-text-muted);
    font-size: 0.875rem;
    font-weight: 500;
}

.spinner {
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(108, 62, 148, 0.12);
    border-top-color: var(--ps-brand);
    border-radius: 50%;
    animation: ps-spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    flex: none;
}

.text {
    letter-spacing: 0.01em;
}

@keyframes ps-spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
