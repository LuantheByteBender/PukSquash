<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

import { loginUser } from '../features/auth/authService';
import { refreshAccount } from '../composables/useAccount.js';

const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const message = ref('');
const isLoggingIn = ref(false);

if (typeof route.query.email === 'string' && route.query.email) {
    email.value = route.query.email;
}

const statePassword = route.state?.password ?? window.history.state?.password;
if (typeof statePassword === 'string' && statePassword) {
    password.value = statePassword;
}

async function login() {
    message.value = '';
    isLoggingIn.value = true;

    try {
        const result = await loginUser(
            email.value,
            password.value
        );

        if (!result.user.emailVerified) {
            message.value =
                'Please verify your email before logging in.';
            return;
        }

        message.value =
            `Logged in successfully as ${result.user.email}`;

        try {
            const profile = await refreshAccount();
            const role = profile?.role ?? null;

            if (role) {
                message.value = `Logged in successfully as ${result.user.email} (${role})`;
            }

            if (role === 'COACH') {
                await router.push({ name: 'coach-bookings' });
            } else {
                await router.push({ name: 'book' });
            }
        } catch (error) {
            message.value = error.message;
            return;
        }
    } catch (error) {
        message.value = error.message;
    } finally {
        isLoggingIn.value = false;
    }
}


</script>

<template>
    <main class="login-page">

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
                    <input
                        id="password"
                        v-model="password"
                        type="password"
                        placeholder="Password"
                        required
                    />

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

.background-image {
    position: absolute;
    inset: 0;
    z-index: -1;
    background-color: #17121d;
}

.background-image img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    opacity: 0.72;
}

@media (max-width: 768px) {
    .background-image img {
        object-position: 80% center;
    }

    .screen-header img {
        width: clamp(13rem, 50vw, 18rem);
        height: clamp(13rem, 50vw, 18rem);
    }

    .screen-header h1 {
        font-size: clamp(2.75rem, 9vw, 3.75rem);
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
}

.screen-header img {
    width: clamp(12rem, 40vw, 20rem);
    height: clamp(12rem, 40vw, 20rem);
    object-fit: contain;
    transform: translateY(-1.5rem);
}

.screen-header h1 {
    margin: -0.5rem 0 0;
    color: white;
    font-size: clamp(2.25rem, 6vw, 4rem);
    line-height: 1.1;
    transform: translateY(-4rem);
}

.login-content {
    align-items: center;
    min-height: 0;
}

.auth-card {
    width: min(100%, 26rem);
    padding: clamp(1.25rem, 3vw, 1.75rem);
    border-radius: 0.9rem;
    background: rgb(255 255 255 / 0.92);
    box-shadow: 0 18px 40px rgb(0 0 0 / 0.28);
    color: #17121d;
}

.auth-card a {
    color: inherit;
}

@media (min-width: 769px) {
    #topscreen {
        transform: translateY(clamp(-5rem, -7vh, -2.5rem));
    }

    .login-content {
        transform: translateY(clamp(-5rem, -7vh, -2.5rem));
    }
}

.login-fields {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 0.5rem;
}

.login-fields label {
    color: inherit;
    font-size: 1rem;
    font-weight: 700;
}

.login-fields input {
    width: 100%;
    min-height: 2.75rem;
    margin-bottom: 0.75rem;
    padding: 0.7rem 0.8rem;
    box-sizing: border-box;
    border: 1px solid rgb(23 18 29 / 0.28);
    border-radius: 0.4rem;
    color: #17121d;
    background: white;
    font: inherit;
}

.login-fields input::placeholder {
    color: rgb(23 18 29 / 0.55);
}

.login-fields input:focus {
    outline: 2px solid rgb(23 18 29 / 0.65);
    outline-offset: 2px;
    background: white;
}

.login-message {
    margin: 0.25rem 0 0;
    color: inherit;
    text-align: center;
}

.login-buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
}

.login-buttons button {
    flex: 1 1 auto;
    width: 100%;
    min-height: 2.75rem;
    padding: 0.7rem 1rem;
    border: 1px solid white;
    border-radius: 0.4rem;
    color: white;
    background-color: rgb(108, 61, 145);
    font: inherit;
    font-weight: 700;
    cursor: pointer;
    transition: background-color 180ms ease, transform 180ms ease;
}

.login-buttons button:hover:not(:disabled),
.login-buttons button:focus-visible {
    background-color: rgb(84, 43, 117);
    transform: translateY(-2px);
}

.login-buttons button:disabled {
    cursor: wait;
    opacity: 0.7;
}

@media (max-width: 30rem) {
    .login-page {
        padding-block: 1rem;
    }

    .login-buttons button {
        flex-basis: 100%;
    }
}


</style>