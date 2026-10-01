<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useRoute } from 'vue-router';

import { http, ApiError } from '../services/http.js';

const router = useRouter();
const route = useRoute();

const fullName = ref('');
const studentNumber = ref('');
const phoneNumber = ref('');

const message = ref('');
const submitting = ref(false);

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

        submitPayfastForm(processUrl, fields);
    } catch (e) {
        message.value = e?.message ?? String(e);
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    loadDraft();
    await checkIfAlreadyRegistered();
});
</script>

<template>
    <main class="payment-page">
        <header>
            <h1>Registration Payment</h1>
        </header>

        <section class="auth-card">
            <p class="hint">
                Your account is verified, but registration is not active yet.
                Please complete the registration payment to continue.
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
                    placeholder="Student number"
                />

                <label for="phone-number">Phone Number (optional)</label>
                <input
                    id="phone-number"
                    v-model="phoneNumber"
                    type="tel"
                    placeholder="Phone number"
                    autocomplete="tel"
                />

                <button type="submit" :disabled="submitting">
                    {{ submitting ? 'Redirecting...' : 'Pay with PayFast (Sandbox)' }}
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
