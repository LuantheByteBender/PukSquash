<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';

import {
    registerUser,
} from '../features/auth/authService';
import { useToast } from '../composables/useToast.js';
import { useAuth } from '../composables/useAuth.js';

const router = useRouter();
const toast = useToast();
const { isSignedIn, emailVerified, ready } = useAuth();

const email = ref('');
const password = ref('');
const confirmPassword = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);
const message = ref('');
const isWaitingForVerification = ref(false);
const submitting = ref(false);

let verificationTimer = null;
let verificationToastId = null;

function stopVerificationTimer() {
    if (verificationTimer) {
        clearInterval(verificationTimer);
        verificationTimer = null;
    }

    if (verificationToastId !== null) {
        toast.dismiss(verificationToastId);
        verificationToastId = null;
    }

    isWaitingForVerification.value = false;
}

async function register() {
    if (submitting.value || isWaitingForVerification.value) return;

    message.value = '';
    if (password.value !== confirmPassword.value) {
        message.value = 'Passwords do not match.';
        toast.error(message.value);
        return;
    }

    submitting.value = true;

    try {
        const result = await registerUser(
            email.value,
            password.value
        );

        message.value = `Account created for ${result.user.email}. Check your email for the verification link.`;

        stopVerificationTimer();
        isWaitingForVerification.value = true;
        verificationToastId = toast.loading('Waiting for email verification...');
        verificationTimer = setInterval(async () => {
            try {
                const { auth } = await import('../services/firebase.js');
                const user = auth.currentUser;

                if (!user) {
                    stopVerificationTimer();
                    return;
                }

                await user.reload();

                if (!user.emailVerified) {
                    return;
                }

                stopVerificationTimer();
                toast.success('Email verified. Continue with your payment.');
                await router.push({ name: 'registration-payment' });
            } catch {
            }
        }, 3000);
    } catch (error) {
        if (error?.message === 'An account already exists for this email address.') {
            message.value = 'An account already exists for this email address. Please log in to complete your registration payment.';
            toast.info(message.value);
        } else {
            message.value = error?.message ?? 'Unable to create your account.';
            toast.error(message.value);
        }
        submitting.value = false;
    }
}

onMounted(async () => {
    if (!ready.value) {
        await new Promise((resolve) => {
            const stop = watch(ready, (value) => {
                if (!value) return;
                stop();
                resolve();
            });
        });
    }

    if (isSignedIn.value && emailVerified.value) {
        await router.replace({ name: 'registration-payment' });
    }
});

onUnmounted(() => {
    stopVerificationTimer();
});
</script>

<template>
    <main class="signup-page">

        <div class="brand-mark">
            <span class="brand-mark__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <ellipse cx="12" cy="8" rx="5" ry="7"></ellipse>
                    <line x1="12" y1="1" x2="12" y2="15"></line>
                    <line x1="7" y1="8" x2="17" y2="8"></line>
                    <line x1="12" y1="15" x2="12" y2="22"></line>
                </svg>
            </span>
            <span>PukSquash</span>
        </div>

        <!-- Background image of entire page -->
        <div class="background-image">
            <img
                :src="'/images/SignUpBG.png'"
                alt="Background Image"
            />
        </div>

        <!-- Page Heading -->
        <header>
            <h1>Sign Up</h1>
        </header>

        <form id="signup-form" @submit.prevent="register">

            <div class="auth-card">
                <!-- Input section: Email & Password -->
                <section id="signup-section">
                    <div class="input-fields">
                        <label for="email">Email</label>
                        <input
                            id="email"
                            v-model="email"
                            type="email"
                            placeholder="NWU email"
                            autocomplete="email"
                            required
                        >

                        <label for="password">Password</label>
                        <div class="password-input">
                            <input
                                id="password"
                                v-model="password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="Password"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                @click="showPassword = !showPassword"
                            >
                                <svg
                                    v-if="showPassword"
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <line x1="4" y1="4" x2="20" y2="20" />
                                </svg>
                                <svg
                                    v-else
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>

                        <label for="confirm-password">Confirm Password</label>
                        <div class="password-input">
                            <input
                                id="confirm-password"
                                v-model="confirmPassword"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                placeholder="Confirm Password"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'"
                                @click="showConfirmPassword = !showConfirmPassword"
                            >
                                <svg
                                    v-if="showConfirmPassword"
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <line x1="4" y1="4" x2="20" y2="20" />
                                </svg>
                                <svg
                                    v-else
                                    aria-hidden="true"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- Page buttons -->
                <section id="buttons-section">
                    <div class="signUp-buttons">
                        <button type="submit" :disabled="submitting || isWaitingForVerification">
                            Register
                        </button>

                        <RouterLink
                            class="back-link"
                            :to="{ name: 'login' }"
                        >
                            Back to Login
                        </RouterLink>
                    </div>

                    <p v-if="message" role="status" aria-live="polite">
                        {{ message }}
                    </p>

                    <p v-if="isWaitingForVerification" role="status" aria-live="polite">
                        Waiting for email verification...
                    </p>
                </section>
            </div>
        </form>

    </main>

</template> 

<style scoped>

.signup-page {
    position: relative;
    display: grid;
    grid-template-rows: auto minmax(0, 1fr) auto;
    min-height: 100svh;
    overflow: hidden;
    padding: clamp(1.5rem, 4vw, 3rem) clamp(1rem, 4vw, 3rem);
    box-sizing: border-box;
    color: white;
    isolation: isolate;
}

.brand-mark {
    position: absolute;
    top: 1.25rem;
    left: 1.5rem;
    font-size: 1.2rem;
    font-weight: 800;
    letter-spacing: -0.02em;
    color: #fff;
    z-index: 2;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.brand-mark__icon {
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(4px);
}

/* Background image */

.background-image {
    position: absolute;
    inset: 0;
    z-index: -1;
    background-color: #17121d;
}

.background-image::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(108, 62, 148, 0.3) 0%, rgba(23, 18, 29, 0.4) 50%, rgba(61, 33, 84, 0.35) 100%);
    z-index: 1;
}

.background-image img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    opacity: 0.65;
}

/* Page heading */

header {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
    animation: signup-slide-down 0.5s cubic-bezier(0.4, 0, 0.2, 1) both;
}

header h1 {
    margin: 0;
    color: white;
    font-size: clamp(2rem, 5.5vw, 3.5rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    text-align: center;
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
}

.back-link {
    display: grid;
    place-items: center;
    width: 100%;
    min-height: 2.75rem;
    padding: 0.7rem 1rem;
    box-sizing: border-box;
    border: 1.5px solid rgba(108, 61, 145, 0.5);
    border-radius: 10px;
    color: #6c3e94;
    background: transparent;
    font: inherit;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.back-link:hover,
.back-link:focus-visible {
    background-color: rgba(108, 61, 145, 0.08);
    border-color: rgba(108, 61, 145, 0.7);
    transform: translateY(-1px);
}

/* Signup form */

#signup-form {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 0;
    animation: signup-slide-up 0.5s cubic-bezier(0.4, 0, 0.2, 1) 0.1s both;
}

.auth-card {
    width: min(100%, 24rem);
    padding: clamp(1.5rem, 3vw, 2rem);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(12px);
    box-shadow:
        0 20px 50px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.1) inset;
    color: #1c1922;
}

/* Input section */

#signup-section {
    display: flex;
    justify-content: center;
    width: 100%;
}

.input-fields {
    display: flex;
    flex-direction: column;
    width: min(100%, 22rem);
    gap: 0.4rem;
}

.input-fields label {
    color: #1c1922;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.input-fields input {
    width: 100%;
    min-height: 2.75rem;
    margin-bottom: 0.6rem;
    padding: 0.7rem 0.85rem;
    box-sizing: border-box;
    border: 1.5px solid rgba(23, 18, 29, 0.18);
    border-radius: 10px;
    color: #1c1922;
    background: rgba(255, 255, 255, 0.8);
    font: inherit;
    font-size: 0.95rem;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.input-fields input::placeholder {
    color: rgba(23, 18, 29, 0.4);
}

.input-fields input:focus {
    outline: none;
    border-color: #6c3e94;
    box-shadow: 0 0 0 3px rgba(108, 62, 148, 0.15);
    background: white;
}

.password-input {
    position: relative;
    width: 100%;
    margin-bottom: 0.6rem;
}

.password-input input {
    padding-right: 4.25rem;
    margin-bottom: 0;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 0.6rem;
    transform: translateY(-50%);
    padding: 0.25rem 0.45rem;
    border: 0;
    border-radius: 8px;
    background: rgba(23, 18, 29, 0.06);
    color: rgba(23, 18, 29, 0.8);
    font: inherit;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
}

.password-toggle:hover,
.password-toggle:focus-visible {
    background: rgba(23, 18, 29, 0.1);
}

.password-toggle:active {
    background: rgba(23, 18, 29, 0.14);
}

.password-toggle svg {
    width: 18px;
    height: 18px;
    display: block;
}

/* Signup buttons */

#buttons-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    margin-top: 1.25rem;
}

.signUp-buttons {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    width: min(100%, 22rem);
}

.signUp-buttons button {
    width: 100%;
    min-height: 2.85rem;
    padding: 0.75rem 1.25rem;
    border: none;
    border-radius: 10px;
    color: white;
    background: linear-gradient(135deg, #7b4fa6 0%, #6C3D91 50%, #56306f 100%);
    font: inherit;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(108, 61, 145, 0.3);
}

.signUp-buttons button::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 60%);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.signUp-buttons button:hover::after {
    opacity: 1;
}

.signUp-buttons button:hover,
.signUp-buttons button:focus-visible {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 61, 145, 0.4);
}

.signUp-buttons button:active {
    transform: translateY(0) scale(0.98);
    box-shadow: 0 2px 8px rgba(108, 61, 145, 0.25);
}

/* Signup message */

#buttons-section p {
    margin: 0.75rem 0 0;
    color: inherit;
    text-align: center;
    max-width: 28rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

/* Bottom buttons outside the form */

.signup-page > button {
    display: block;
    width: min(100%, 22rem);
    min-height: 2.85rem;
    margin: 0.75rem auto 0;
    padding: 0.75rem 1.25rem;
    box-sizing: border-box;
    border: none;
    border-radius: 10px;
    color: white;
    background: linear-gradient(135deg, #7b4fa6 0%, #6C3D91 50%, #56306f 100%);
    font: inherit;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 14px rgba(108, 61, 145, 0.3);
}

.signup-page > button:hover,
.signup-page > button:focus-visible {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 61, 145, 0.4);
}

.signup-page > button:active {
    transform: translateY(0) scale(0.98);
}

/* Desktop positioning */

@media (min-width: 769px) {
    header {
        transform: translateY(clamp(-2rem, -3vh, -1rem));
    }

    #signup-form {
        transform: translateY(clamp(-3rem, -5vh, -1.5rem));
    }
}

/* Mobile */

@media (max-width: 768px) {
    .background-image img {
        object-position: 80% center;
    }

    header h1 {
        font-size: clamp(2.25rem, 9vw, 3.25rem);
    }
}

@media (max-width: 30rem) {
    .signup-page {
        padding-block: 1rem;
    }

    .signUp-buttons {
        width: min(100%, 22rem);
    }

    .signup-page > button {
        width: min(100%, 22rem);
    }
}

@keyframes signup-slide-down {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes signup-slide-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

</style>

