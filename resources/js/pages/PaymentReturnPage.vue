<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import { http, ApiError } from '../services/http.js';
import { refreshAccount, clearAccount } from '../composables/useAccount.js';
import { logoutUser } from '../features/auth/authService';

const router = useRouter();

const RETRY_DELAYS_MS = [2000, 3000, 5000, 8000, 12000];

const message = ref('Processing your payment...');
const loading = ref(true);

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

async function reconcileWithRetry() {
    for (let attempt = 0; attempt <= RETRY_DELAYS_MS.length; attempt++) {
        try {
            await http.post('/registration/reconcile');
            return;
        } catch (e) {
            const isStillProcessing =
                (e instanceof ApiError && e.code === 'REGISTRATION_PAYMENT_REQUIRED') ||
                e?.status === 409;

            if (!isStillProcessing || attempt === RETRY_DELAYS_MS.length) {
                throw e;
            }

            const delay = RETRY_DELAYS_MS[attempt];
            const seconds = Math.round(delay / 1000);
            message.value = `Payment received — confirming registration (retry ${attempt + 1}/${RETRY_DELAYS_MS.length}, waiting ${seconds}s)...`;
            await sleep(delay);
        }
    }
}

async function processReturn() {
    loading.value = true;
    message.value = 'Processing your payment...';

    try {
        try {
            await http.post('/registration/payment/demo/complete');
        } catch {
        }

        await reconcileWithRetry();

        const profile = await refreshAccount();
        const role = profile?.role ?? null;

        let redirect = null;
        try {
            redirect = localStorage.getItem('registration_payment_redirect');
            localStorage.removeItem('registration_payment_redirect');
            localStorage.removeItem('registration_payment_draft');
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
        if (
            (e instanceof ApiError && e.code === 'REGISTRATION_PAYMENT_REQUIRED') ||
            e?.status === 409
        ) {
            message.value = 'Payment is still being confirmed by the payment provider. Please click "Check again" in a moment.';
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
