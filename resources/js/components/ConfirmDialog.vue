<script setup>
const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: 'Confirm' },
    message: { type: String, default: '' },
    confirmText: { type: String, default: 'Confirm' },
    cancelText: { type: String, default: 'Cancel' },
    confirmVariant: { type: String, default: 'primary' },
});

const emit = defineEmits(['confirm', 'cancel']);

function onBackdropClick() {
    emit('cancel');
}

function onCancel() {
    emit('cancel');
}

function onConfirm() {
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

            <div class="actions">
                <button type="button" class="btn btn-secondary" @click="onCancel">
                    {{ cancelText }}
                </button>
                <button type="button" :class="confirmClass()" @click="onConfirm">
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
    background: rgba(24, 14, 33, 0.55);
    display: grid;
    place-items: center;
}

.dialog {
    max-width: 520px;
    width: 100%;
    background: var(--ps-surface);
    border-radius: var(--ps-radius);
    padding: 1.1rem;
    border: 1px solid var(--ps-border-soft);
    box-shadow: 0 10px 30px rgba(24, 14, 33, 0.25);
}

.dialog h2 {
    margin: 0 0 0.5rem;
    font-size: 1.15rem;
}

.actions {
    display: flex;
    gap: 0.6rem;
    justify-content: flex-end;
    margin-top: 1rem;
    flex-wrap: wrap;
}
</style>
