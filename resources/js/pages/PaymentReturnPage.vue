<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import { http, ApiError } from '../services/http.js';
import { refreshAccount, clearAccount } from '../composables/useAccount.js';
import { logoutUser } from '../features/auth/authService';

const router = useRouter();

const message = ref('Processing your payment...');
const loading = ref(true);

async function processReturn() {
    loading.value = true;

    try {
        try {
            await http.post('/registration/payment/demo/complete');
        } catch {
        }

        await http.post('/registration/reconcile');

        const profile = await refreshAccount();
        const role = profile?.role ?? null;

        let redirect = null;
        try {
            redirect = localStorage.getItem('registration_payment_redirect');
            localStorage.removeItem('registration_payment_redirect');
        } catch {
        }

        if (redirect) {
            await router.replace(redirect);
            return;
        }

        if (role === 'COACH') {
            await router.replace({ name: 'coach-bookings' });
            return;
        }

        await router.replace({ name: 'book' });
    } catch (e) {
        if (e instanceof ApiError && e.code === 'REGISTRATION_PAYMENT_REQUIRED') {
            message.value = 'Payment is still processing. Please wait a moment and refresh this page.';
            return;
        }

        if (e instanceof ApiError && e.code === 'EMAIL_NOT_VERIFIED') {
            message.value = 'Please verify your email address before completing payment.';
            return;
        }

        if (e?.status === 401) {
            try {
                await logoutUser();
            } catch {
            }
            clearAccount();
            await router.replace({ name: 'login' });
            return;
        }

        message.value = e?.message ?? String(e);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    processReturn();
});
</script>

<template>
    <main class="return-page">
        <section class="card">
            <h1>Payment Processing</h1>
            <p>{{ message }}</p>
            <div class="actions">
                <button type="button" :disabled="loading" @click="processReturn">
                    {{ loading ? 'Checking...' : 'Check again' }}
                </button>
                <button type="button" class="secondary" @click="router.push({ name: 'registration-payment' })">
                    Back to payment
                </button>
            </div>
        </section>
    </main>
</template>

<style scoped>
.return-page {
    min-height: 100svh;
    display: grid;
    place-items: center;
    padding: 2rem 1.25rem;
    background: #f3f4f6;
}

.card {
    width: min(520px, 100%);
    background: white;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

h1 {
    margin: 0 0 0.5rem;
    font-size: 1.5rem;
    font-weight: 900;
}

p {
    margin: 0 0 1rem;
    color: #374151;
}

.actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

button {
    padding: 0.75rem 0.9rem;
    border-radius: 10px;
    border: none;
    background: #6c3e94;
    color: white;
    font-weight: 800;
    cursor: pointer;
}

button.secondary {
    background: #e5e7eb;
    color: #111827;
}

button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}
</style>
