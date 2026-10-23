import { reactive, readonly } from 'vue';

/**
 * Small global message queue.
 *
 * Every booking action must end in a clear success or error message. A student
 * walking to the courts should never be unsure whether their booking exists.
 */

const state = reactive({ items: [] });

let nextId = 1;

function push(type, message, ttl) {
    const normalizedMessage = String(message ?? '').trim();
    const existing = state.items.find((item) => item.type === type && item.message === normalizedMessage);

    if (existing) {
        return existing.id;
    }

    const id = nextId++;

    state.items.push({ id, type, message: normalizedMessage });

    if (ttl !== 0) {
        setTimeout(() => dismiss(id), ttl ?? 5000);
    }

    return id;
}

function dismiss(id) {
    const index = state.items.findIndex((item) => item.id === id);

    if (index !== -1) {
        state.items.splice(index, 1);
    }
}

export function useToast() {
    return {
        toasts: readonly(state),
        dismiss,
        success: (message, ttl) => push('success', message, ttl),
        error: (message, ttl) => push('error', message, ttl ?? 7000),
        warn: (message, ttl) => push('warn', message, ttl ?? 7000),
        info: (message, ttl) => push('info', message, ttl),
        loading: (message, ttl = 0) => push('loading', message, ttl),
    };
}
