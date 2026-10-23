import { ref, computed, readonly } from 'vue';

const profile = ref(null);
const loading = ref(false);
const error = ref(null);

export async function refreshAccount() {
    loading.value = true;
    error.value = null;

    try {
        const { getCurrentUser } = await import('../services/api.js');
        profile.value = await getCurrentUser();
        return profile.value;
    } catch (e) {
        error.value = e?.message ?? String(e);
        throw e;
    } finally {
        loading.value = false;
    }
}

export async function updateAccount(payload) {
    loading.value = true;
    error.value = null;

    try {
        const { http } = await import('../services/http.js');
        profile.value = await http.patch('/me', payload);
        return profile.value;
    } catch (e) {
        error.value = e?.message ?? String(e);
        throw e;
    } finally {
        loading.value = false;
    }
}

export async function addAccountFunds(amountCents) {
    loading.value = true;
    error.value = null;

    try {
        const { http } = await import('../services/http.js');
        const result = await http.post('/me/funds', { amount_cents: amountCents });
        profile.value = result.user ?? profile.value;
        return result;
    } catch (e) {
        error.value = e?.message ?? String(e);
        throw e;
    } finally {
        loading.value = false;
    }
}

export async function updatePrivateCoachingFee(amountCents) {
    loading.value = true;
    error.value = null;

    try {
        const { http } = await import('../services/http.js');
        const result = await http.patch('/me/private-coaching-fee', { private_coaching_fee_cents: amountCents });
        profile.value = result.user ?? profile.value;
        return result;
    } catch (e) {
        error.value = e?.message ?? String(e);
        throw e;
    } finally {
        loading.value = false;
    }
}

export function clearAccount() {
    profile.value = null;
    error.value = null;
    loading.value = false;
}

export function useAccount() {
    return {
        profile: readonly(profile),
        role: computed(() => profile.value?.role ?? null),
        loading: readonly(loading),
        error: readonly(error),
        refreshAccount,
        updateAccount,
        addAccountFunds,
        updatePrivateCoachingFee,
        clearAccount,
    };
}
