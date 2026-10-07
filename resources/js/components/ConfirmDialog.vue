<script setup>
import LoadingSpinner from './LoadingSpinner.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Confirm' },
    message: { type: String, default: '' },
    confirmText: { type: String, default: 'Confirm' },
    cancelText: { type: String, default: 'Cancel' },
    confirmVariant: { type: String, default: 'primary' },
    loading: { type: Boolean, default: false },
    loadingText: { type: String, default: 'Working…' },
});

const emit = defineEmits(['confirm', 'cancel']);

function onBackdropClick() {
    if (props.loading) return;
    emit('cancel');
}

function onCancel() {
    if (props.loading) return;
    emit('cancel');
}

function onConfirm() {
    if (props.loading) return;
    emit('confirm');
}

function confirmClass() {
    if (props.confirmVariant === 'danger') return 'btn btn-danger';
    if (props.confirmVariant === 'secondary') return 'btn btn-secondary';
    return 'btn';
}
</script>

<template>
    <div v-if="open" class="backdrop" @click.self="onBackdropClick">
        <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
            <h2 id="confirm-title">{{ title }}</h2>
            <p class="muted">{{ message }}</p>

            <div v-if="loading" class="loading">
                <LoadingSpinner :label="loadingText" />
            </div>

            <div class="actions">
                <button type="button" class="btn btn-secondary" :disabled="loading" @click="onCancel">
                    {{ cancelText }}
                </button>
                <button type="button" :class="confirmClass()" :disabled="loading" @click="onConfirm">
                    {{ confirmText }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.backdrop {
    position: fixed;
    inset: 0;
    z-index: 80;
    padding: 1rem;
    background: rgba(23, 18, 29, 0.55);
    backdrop-filter: blur(4px);
    display: grid;
    place-items: center;
    animation: confirm-fade-in 0.2s ease;
}

.dialog {
    max-width: 480px;
    width: 100%;
    background: var(--ps-surface-elevated, #fff);
    border-radius: var(--ps-radius-lg, 16px);
    padding: 1.5rem;
    border: 1px solid var(--ps-border-soft);
    box-shadow: var(--ps-shadow-xl);
    animation: confirm-scale-in 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.dialog h2 {
    margin: 0 0 0.4rem;
    font-size: 1.1rem;
    font-weight: 700;
}

.loading {
    margin-top: 1rem;
    display: grid;
    place-items: center;
}

.actions {
    display: flex;
    gap: 0.6rem;
    justify-content: flex-end;
    margin-top: 1.25rem;
    flex-wrap: wrap;
}

@keyframes confirm-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes confirm-scale-in {
    from { opacity: 0; transform: scale(0.92); }
    to { opacity: 1; transform: scale(1); }
}
</style>
