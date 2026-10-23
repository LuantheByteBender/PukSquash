<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useRoute } from 'vue-router';

import { http, ApiError } from '../services/http.js';

const router = useRouter();
const route = useRoute();

const fullName = ref('');
const studentNumber = ref('');
const phoneNumber = ref('');

const registrationFee = ref(null);

const message = ref('');
const submitting = ref(false);
const initializing = ref(false);

const overlayOpen = computed(() => initializing.value || submitting.value);
const overlayText = computed(() => (submitting.value ? 'Redirecting to PayFast...' : 'Preparing payment...'));

function loadDraft() {
    try {
        const raw = localStorage.getItem('registration_payment_draft');
        if (!raw) return;

        const draft = JSON.parse(raw);
        if (draft && typeof draft === 'object') {
            if (typeof draft.full_name === 'string') fullName.value = draft.full_name;
            if (typeof draft.student_number === 'string') studentNumber.value = draft.student_number;
            if (typeof draft.phone_number === 'string') phoneNumber.value = draft.phone_number;
        }
    } catch {
    }
}

function saveDraft() {
    try {
        localStorage.setItem(
            'registration_payment_draft',
            JSON.stringify({
                full_name: fullName.value,
                student_number: studentNumber.value,
                phone_number: phoneNumber.value,
            })
        );

        if (typeof route.query.redirect === 'string' && route.query.redirect) {
            localStorage.setItem('registration_payment_redirect', route.query.redirect);
        }
    } catch {
    }
}

function submitPayfastForm(processUrl, fields) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = processUrl;

    Object.entries(fields || {}).forEach(([k, v]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = k;
        input.value = String(v);
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

async function checkIfAlreadyRegistered() {
    try {
        const status = await http.get('/registration/status');
        registrationFee.value = status?.registration_fee ?? null;
        if (status?.registered) {
            await router.replace({ name: 'book' });
        }
    } catch (e) {
        if (e instanceof ApiError && e.code === 'EMAIL_NOT_VERIFIED') {
            message.value = 'Please verify your email address before making payment.';
            return;
        }
    }
}

function clearDraft() {
    try {
        localStorage.removeItem('registration_payment_draft');
        localStorage.removeItem('registration_payment_redirect');
    } catch {
    }
}

async function attemptDemoCompletionIfPossible() {
    try {
        const status = await http.get('/registration/status');
        if (status?.registered) {
            await router.replace({ name: 'book' });
            return;
        }

        const latest = status?.latest_payment ?? null;
        if (!latest || typeof latest !== 'object') {
            return;
        }

        if (String(latest.status ?? '').toUpperCase() === 'PAID') {
            await http.post('/registration/reconcile');
            await router.replace({ name: 'book' });
            return;
        }

        try {
            await http.post('/registration/payment/demo/complete');
        } catch {
            return;
        }

        await http.post('/registration/reconcile');
        await router.replace({ name: 'book' });
    } catch (e) {
        if (e instanceof ApiError && e.code === 'EMAIL_NOT_VERIFIED') {
            message.value = 'Please verify your email address before making payment.';
        }
    }
}

async function initiatePayment() {
    if (submitting.value) return;

    message.value = '';
    submitting.value = true;

    saveDraft();

    try {
        const result = await http.post('/registration/payment/initiate', {
            full_name: fullName.value,
            student_number: studentNumber.value || null,
            phone_number: phoneNumber.value || null,
        });

        const processUrl = result?.payfast?.process_url;
        const fields = result?.payfast?.fields;

        if (!processUrl || !fields) {
            message.value = 'Payment could not be started. Please try again.';
            return;
        }

        clearDraft();
        fullName.value = '';
        studentNumber.value = '';
        phoneNumber.value = '';
        submitPayfastForm(processUrl, fields);
    } catch (e) {
        message.value = e?.message ?? String(e);
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    initializing.value = true;

    try {
        loadDraft();
        await checkIfAlreadyRegistered();
        await attemptDemoCompletionIfPossible();
    } finally {
        initializing.value = false;
    }
});
</script>

<template>
    <main class="payment-page">
        <div v-if="overlayOpen" class="redirect-overlay" aria-live="polite" aria-busy="true">
            <div class="redirect-card">
                <span class="spinner" aria-hidden="true" />
                <p>{{ overlayText }}</p>
            </div>
        </div>

        <header>
            <h1>Registration Payment</h1>
        </header>

        <section class="auth-card">
            <p class="hint">
                Your account is verified, but registration is not active yet.
                Please complete the registration payment to continue.
            </p>

            <p v-if="registrationFee" class="fee">
                Amount due: {{ registrationFee.currency }} {{ registrationFee.amount }}
            </p>

            <form class="payment-form" @submit.prevent="initiatePayment">
                <label for="full-name">Full Name</label>
                <input
                    id="full-name"
                    v-model="fullName"
                    type="text"
                    placeholder="Full name"
                    autocomplete="name"
                    required
                />

                <label for="student-number">Student Number (optional)</label>
                <input
                    id="student-number"
                    v-model="studentNumber"
                    type="text"
                    placeholder="7-digit student number"
                    inputmode="numeric"
                    pattern="\d{7}"
                    maxlength="7"
                />

                <label for="phone-number">Phone Number (optional)</label>
                <input
                    id="phone-number"
                    v-model="phoneNumber"
                    type="tel"
                    placeholder="10-digit phone number"
                    autocomplete="tel"
                    inputmode="numeric"
                    pattern="\d{10}"
                    maxlength="10"
                />

                <button type="submit" :disabled="overlayOpen">
                    {{ overlayOpen ? overlayText : `Pay with PayFast (Sandbox) - ${registrationFee?.currency ?? 'ZAR'} ${registrationFee?.amount ?? ''}` }}
                </button>

                <p v-if="message" class="message" role="status">
                    {{ message }}
                </p>
            </form>
        </section>
    </main>
</template>

<style scoped>
.payment-page {
    min-height: 100svh;
    padding: 2rem 1.25rem;
    color: #111827;
    background: #f3f4f6;
}

.redirect-overlay {
    position: fixed;
    inset: 0;
    display: grid;
    place-items: center;
    background: rgba(17, 24, 39, 0.45);
    backdrop-filter: blur(6px);
    z-index: 50;
}

.redirect-card {
    width: min(420px, calc(100% - 2.5rem));
    background: rgba(255, 255, 255, 0.95);
    border-radius: 14px;
    padding: 1.25rem;
    display: grid;
    justify-items: center;
    gap: 0.75rem;
    box-shadow: 0 14px 40px rgba(0, 0, 0, 0.18);
    color: #111827;
}

.redirect-card p {
    margin: 0;
    font-weight: 800;
}

.spinner {
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: 4px solid rgba(108, 62, 148, 0.2);
    border-top-color: #6c3e94;
    animation: spin 0.9s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

header {
    max-width: 520px;
    margin: 0 auto 1.25rem;
}

h1 {
    font-size: 1.75rem;
    font-weight: 800;
    margin: 0;
}

.auth-card {
    max-width: 520px;
    margin: 0 auto;
    background: white;
    padding: 1.25rem;
    border-radius: 14px;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.hint {
    margin: 0 0 1rem;
    color: #374151;
}

.fee {
    margin: -0.25rem 0 1rem;
    font-weight: 800;
    color: #111827;
}

.payment-form {
    display: grid;
    gap: 0.6rem;
}

label {
    font-size: 0.9rem;
    font-weight: 700;
}

input {
    padding: 0.75rem 0.8rem;
    border-radius: 10px;
    border: 1px solid #d1d5db;
    font-size: 1rem;
}

button {
    margin-top: 0.75rem;
    padding: 0.75rem 0.9rem;
    border-radius: 10px;
    border: none;
    background: #6c3e94;
    color: white;
    font-weight: 800;
    cursor: pointer;
}

button:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.message {
    margin: 0.75rem 0 0;
    color: #b91c1c;
}
</style>
