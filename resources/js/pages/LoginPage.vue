<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

import { loginUser } from '../features/auth/authService';
import { getCurrentUser } from '../services/api.js';

// Allows this page to ask App.vue to return to the landing page.
const emit = defineEmits(['go-home']);

const router = useRouter();

const email = ref('');
const password = ref('');
const message = ref('');
const isLoggingIn = ref(false);

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
            await getCurrentUser();
        } catch (error) {
            message.value = error.message;
            return;
        }

        await router.push({ name: 'book' });
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
        </section>


        <!-- Bottom section of screen: Login button and back button -->
        <section id="bottom-screen">
            <div class="login-buttons">
                <button type="submit" form="login-form" :disabled="isLoggingIn">
                    {{ isLoggingIn ? 'Logging in...' : 'Login' }}
                </button>

                <button type="button" @click="emit('go-home')">
                    Back to Landing Page
                </button>
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
.login-content,
#bottom-screen {
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
    width: min(100%, 22rem);
    gap: 0.5rem;
}

.login-fields label {
    color: white;
    font-size: 1rem;
    font-weight: 700;
}

.login-fields input {
    width: 100%;
    min-height: 2.75rem;
    margin-bottom: 0.75rem;
    padding: 0.7rem 0.8rem;
    box-sizing: border-box;
    border: 1px solid white;
    border-radius: 0.4rem;
    color: white;
    background: rgb(20 20 20 / 0.72);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    font: inherit;
}

.login-fields input::placeholder {
    color: rgb(255 255 255 / 0.8);
}

.login-fields input:focus {
    outline: 2px solid white;
    outline-offset: 2px;
    background: rgb(20 20 20 / 0.86);
}

.login-message {
    margin: 0.25rem 0 0;
    color: white;
    text-align: center;
}

#bottom-screen {
    align-items: flex-end;
}

.login-buttons {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.75rem;
    width: min(100%, 28rem);
}

.login-buttons button {
    flex: 1 1 10rem;
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