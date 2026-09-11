import { computed, ref } from 'vue';

const activeOperations = ref(0);

export const isLoading = computed(() => activeOperations.value > 0);

export function startLoading() {
    activeOperations.value += 1;
}

export function stopLoading() {
    activeOperations.value = Math.max(0, activeOperations.value - 1);
}
