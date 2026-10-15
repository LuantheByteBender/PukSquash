<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { loginUser, logoutUser } from '../features/auth/authService';
import { refreshAccount, clearAccount } from '../composables/useAccount.js';
import { useToast } from '../composables/useToast.js';
import { useAuth } from '../composables/useAuth.js';

const router = useRouter();
const route = useRoute();

const toast = useToast();
const { isSignedIn, emailVerified, ready, user } = useAuth();

const email = ref('');
const password = ref('');
const showPassword = ref(false);
const message = ref('');
const isLoggingIn = ref(false);

if (typeof route.query.email === 'string' && route.query.email) {
    email.value = route.query.email;
}

const statePassword = route.state?.password ?? window.history.state?.password;
if (typeof statePassword === 'string' && statePassword) {
    password.value = statePassword;
}

async function finishLoginFlow(result, redirect) {
    message.value = `Logged in successfully as ${result.user.email}`;

    try {
        const profile = await refreshAccount();
        const role = profile?.role ?? null;

        if (role) {
            message.value = `Logged in successfully as ${result.user.email} (${role})`;
        }

        toast.success(`Welcome back, ${result.user.email}!`);

        if (redirect) {
            await router.push(redirect);
            return;
        }

        if (role === 'COACH') {
            await router.push({ name: 'coach-bookings' });
            return;
        }

        await router.push({ name: 'book' });
    } catch (error) {
        if (error?.status === 403 && error?.code === 'REGISTRATION_PAYMENT_REQUIRED') {
            toast.info('Please complete registration payment.');
            await router.push(
                redirect
                    ? { name: 'registration-payment', query: { redirect } }
                    : { name: 'registration-payment' }
            );
            return;
        }

        if (error?.status === 401) {
            try {
                await logoutUser();
            } catch {
            }
            clearAccount();
            toast.error(error.message);
            return;
        }

        toast.error(error?.message ?? 'Unable to complete login.');
    }
}

async function login() {
    if (isLoggingIn.value) return;

    message.value = '';
    isLoggingIn.value = true;
    const loginToastId = toast.loading('Signing in…');
    const startedAt = Date.now();

    const redirect = typeof route.query.redirect === 'string' && route.query.redirect
        ? route.query.redirect
        : null;

    try {
        const result = await loginUser(
            email.value,
            password.value
        );

        if (!result.user.emailVerified) {
            message.value = 'Please verify your email before logging in.';
            toast.warn(message.value);
            return;
        }

        await finishLoginFlow(result, redirect);
    } catch (error) {
        toast.error(error?.message ?? 'Unable to sign in.');
    } finally {
        const elapsed = Date.now() - startedAt;
        const minMs = 900;
        if (elapsed < minMs) {
            await new Promise((resolve) => setTimeout(resolve, minMs - elapsed));
        }
        toast.dismiss(loginToastId);
        isLoggingIn.value = false;
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
        const redirect = typeof route.query.redirect === 'string' && route.query.redirect
            ? route.query.redirect
            : null;

        await finishLoginFlow({ user: user.value }, redirect);
    }
});


</script>

<template>
    <main class="login-page">

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
                :src="'/images/LoginPageBackground.png'"
                alt="Background Image"
            />
        </div>


        <!-- Top screen section for logo and heading-->
        <section id="topscreen">
            <div class="screen-header">
                <img
                    :src="'/images/NWU-Logo.png'"
                    alt="North-West University logo"
                >
                <h1>PukSquash</h1>
            </div>
        </section>


        <!-- Middle section of screen: Login fields -->
        <section class="login-content">
            <div class="auth-card">
                <form id="login-form" class="login-fields" @submit.prevent="login">
                    <label for="email">NWU Email</label>
                    <input
                        id="email"
                        v-model="email"
                        type="email"
                        placeholder="NWU Email"
                        required
                    />

                    <label for="password">Password</label>
                    <div class="password-input">
                        <input
                            id="password"
                            v-model="password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="Password"
                            required
                        />
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

                    <!-- Bottom section of screen: Login button and back button -->
                    <div class="login-buttons">
                        <button type="submit" :disabled="isLoggingIn">
                            {{ isLoggingIn ? 'Logging in...' : 'Login' }}
                        </button>
                    </div>

                    <p v-if="message" class="login-message" role="status">
                        {{ message }}
                    </p>

                    <p class="login-message">
                        Don’t have an account?
                        <RouterLink
                            :to="{ name: 'signup' }"
                        >
                            <u>Sign up</u>
                        </RouterLink>
                    </p>
                </form>
            </div>
        </section>

    </main>
</template>

<style scoped>

.login-page {
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

@media (max-width: 768px) {
    .background-image img {
        object-position: 80% center;
    }

    .screen-header img {
        width: clamp(10rem, 45vw, 15rem);
        height: clamp(10rem, 45vw, 15rem);
    }

    .screen-header h1 {
        font-size: clamp(2.25rem, 9vw, 3.25rem);
    }
}


#topscreen,
.login-content {
    display: flex;
    justify-content: center;
    width: 100%;
}

#topscreen {
    align-items: flex-start;
}

.screen-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    animation: login-slide-down 0.6s cubic-bezier(0.4, 0, 0.2, 1) both;
}

.screen-header img {
    width: clamp(10rem, 35vw, 16rem);
    height: clamp(10rem, 35vw, 16rem);
    object-fit: contain;
    transform: translateY(-1rem);
    filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.3));
}

.screen-header h1 {
    margin: -0.5rem 0 0;
    color: white;
    font-size: clamp(2rem, 5.5vw, 3.5rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    line-height: 1.1;
    transform: translateY(-3.5rem);
    text-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
}

.login-content {
    align-items: center;
    min-height: 0;
    animation: login-slide-up 0.5s cubic-bezier(0.4, 0, 0.2, 1) 0.15s both;
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

.auth-card a {
    color: #6c3e94;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.15s ease;
}

.auth-card a:hover {
    color: #56306f;
}

.auth-card a u {
    text-decoration: none;
    border-bottom: 1.5px solid currentColor;
    padding-bottom: 1px;
}

@media (min-width: 769px) {
    #topscreen {
        transform: translateY(clamp(-4.5rem, -6vh, -2rem));
    }

    .login-content {
        transform: translateY(clamp(-4.5rem, -6vh, -2rem));
    }
}

.login-fields {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 0.4rem;
}

.login-fields label {
    color: #1c1922;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.login-fields input {
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

.login-fields input::placeholder {
    color: rgba(23, 18, 29, 0.4);
}

.login-fields input:focus {
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
    display: inline-flex;
    align-items: center;
    justify-content: center;
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

.login-message {
    margin: 0.5rem 0 0;
    color: inherit;
    text-align: center;
    font-size: 0.875rem;
    line-height: 1.5;
}

.login-buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    margin-top: 0.25rem;
}

.login-buttons button {
    flex: 1 1 auto;
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

.login-buttons button::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 60%);
    opacity: 0;
    transition: opacity 0.2s ease;
}

.login-buttons button:hover:not(:disabled)::after {
    opacity: 1;
}

.login-buttons button:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(108, 61, 145, 0.4);
}

.login-buttons button:active:not(:disabled) {
    transform: translateY(0) scale(0.98);
    box-shadow: 0 2px 8px rgba(108, 61, 145, 0.25);
}

.login-buttons button:disabled {
    cursor: wait;
    opacity: 0.65;
}

@media (max-width: 30rem) {
    .login-page {
        padding-block: 1rem;
    }

    .login-buttons button {
        flex-basis: 100%;
    }
}

@keyframes login-slide-down {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes login-slide-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

</style>