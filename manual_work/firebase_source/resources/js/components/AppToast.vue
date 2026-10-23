<script setup>
import { useToast } from '../composables/useToast.js';

const { toasts, dismiss } = useToast();
</script>

<template>
    <div class="toasts" role="status" aria-live="polite">
        <TransitionGroup name="toast">
            <div
                v-for="toast in toasts.items"
                :key="toast.id"
                class="toast"
                :class="`toast-${toast.type}`"
            >
                <span>{{ toast.message }}</span>

                <button class="close" aria-label="Dismiss" @click="dismiss(toast.id)">
                    &times;
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toasts {
    position: fixed;
    right: 1rem;
    bottom: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    z-index: 60;
    max-width: min(420px, calc(100vw - 2rem));
}

.toast {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 0.7rem 0.8rem;
    border-radius: var(--ps-radius-sm);
    border: 1px solid;
    box-shadow: 0 10px 30px rgba(24, 14, 33, 0.18);
    font-size: 0.9rem;
    background: var(--ps-surface);
}

.toast-success {
    background: var(--ps-success-bg);
    border-color: #bfe0c9;
    color: var(--ps-success);
}

.toast-error {
    background: var(--ps-danger-bg);
    border-color: #f3c6c2;
    color: var(--ps-danger);
}

.toast-warn {
    background: var(--ps-warn-bg);
    border-color: #f0d5ab;
    color: var(--ps-warn);
}

.toast-info {
    background: var(--ps-brand-tint);
    border-color: #ddcfe9;
    color: var(--ps-brand);
}

.close {
    border: 0;
    background: none;
    cursor: pointer;
    font-size: 1.2rem;
    line-height: 1;
    color: inherit;
    opacity: 0.6;
    padding: 0;
}

.close:hover {
    opacity: 1;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.18s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(6px);
}
</style>
