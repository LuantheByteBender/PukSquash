<script setup>
import { useToast } from '../composables/useToast.js';
import LoadingSpinner from './LoadingSpinner.vue';

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
                <LoadingSpinner v-if="toast.type === 'loading'" :label="toast.message" />
                <span v-else>{{ toast.message }}</span>

                <button v-if="toast.type !== 'loading'" class="close" aria-label="Dismiss" @click="dismiss(toast.id)">
                    &times;
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toasts {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    z-index: 60;
    width: min(520px, calc(100vw - 2rem));
    pointer-events: none;
}

.toast {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1.15rem 1.5rem;
    border-radius: var(--ps-radius-lg, 16px);
    border: 1px solid;
    box-shadow: var(--ps-shadow-xl);
    font-size: 1.05rem;
    font-weight: 600;
    background: var(--ps-surface);
    backdrop-filter: blur(12px);
    line-height: 1.5;
    pointer-events: auto;
    text-align: center;
}

.toast-success {
    background: rgba(232, 245, 236, 0.95);
    border-color: #bfe0c9;
    color: var(--ps-success);
}

.toast-error {
    background: rgba(253, 236, 234, 0.95);
    border-color: #f3c6c2;
    color: var(--ps-danger);
}

.toast-warn {
    background: rgba(255, 244, 229, 0.95);
    border-color: #f0d5ab;
    color: var(--ps-warn);
}

.toast-info {
    background: rgba(242, 236, 247, 0.95);
    border-color: #ddcfe9;
    color: var(--ps-brand);
}

.toast-loading {
    background: rgba(255, 255, 255, 0.96);
    border-color: #ddcfe9;
    color: var(--ps-brand);
}

.close {
    border: 0;
    background: none;
    cursor: pointer;
    font-size: 1.1rem;
    line-height: 1;
    color: inherit;
    opacity: 0.45;
    padding: 0.1rem;
    border-radius: 4px;
    transition: opacity var(--ps-transition-fast), background var(--ps-transition-fast);
    flex-shrink: 0;
    margin-left: auto;
}

.close:hover {
    opacity: 1;
    background: rgba(0, 0, 0, 0.06);
}

.toast-enter-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-enter-from {
    opacity: 0;
    transform: scale(0.9);
}

.toast-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
</style>
