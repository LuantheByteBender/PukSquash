<script setup>
import { onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';

import {
    registerUser,
} from '../features/auth/authService';

import { getCurrentUser } from '../services/api.js';

const router = useRouter();

const email = ref('');
const password = ref('');
const message = ref('');
const isWaitingForVerification = ref(false);
const submitting = ref(false);

let verificationTimer = null;

function stopVerificationTimer() {
    if (verificationTimer) {
        clearInterval(verificationTimer);
        verificationTimer = null;
    }
}

async function register() {
    if (submitting.value || isWaitingForVerification.value) return;
    submitting.value = true;

    try {
        const result = await registerUser(
            email.value,
            password.value
        );

        try {
            await getCurrentUser();
        } catch {
        }

        message.value = `Account created for ${result.user.email}. Check your email for the verification link.`;
        isWaitingForVerification.value = true;

        stopVerificationTimer();
        verificationTimer = setInterval(async () => {
            try {
                const { auth } = await import('../services/firebase.js');
                const user = auth.currentUser;

                if (!user) {
                    stopVerificationTimer();
                    isWaitingForVerification.value = false;
                    return;
                }

                await user.reload();

                if (!user.emailVerified) {
                    return;
                }

                stopVerificationTimer();
                isWaitingForVerification.value = false;
                await getCurrentUser();
                await router.push({ name: 'book' });
            } catch {
            }
        }, 3000);
    } catch (error) {
        message.value = error.message;
        submitting.value = false;
    }
}

onUnmounted(() => {
    stopVerificationTimer();
});
</script>

<template>
    <main class="signup-page">

        <RouterLink
            class="back-link"
            :to="{ name: 'login' }"
        >
            <u>Back to login</u>
        </RouterLink>

        <!-- Background image of entire page -->
        <div class="background-image">
            <img
                :src="'/images/TempIMG.png'"
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
                        <input
                            id="password"
                            v-model="password"
                            type="password"
                            placeholder="Password"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                </section>

                <!-- Page buttons -->
                <section id="buttons-section">
                    <div class="signUp-buttons">
                        <button type="submit" :disabled="submitting || isWaitingForVerification">
                            Register
                        </button>
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

/* Background image */

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

/* Page heading */

header {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    width: 100%;
}

header h1 {
    margin: 0;
    color: white;
    font-size: clamp(2.25rem, 6vw, 4rem);
    line-height: 1.1;
    text-align: center;
}

.back-link {
    position: absolute;
    top: clamp(1rem, 3vw, 2rem);
    left: clamp(1rem, 3vw, 2rem);
    color: white;
    z-index: 1;
}

/* Signup form */

#signup-form {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
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
    gap: 0.5rem;
}

.input-fields label {
    color: inherit;
    font-size: 1rem;
    font-weight: 700;
}

.input-fields input {
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

.input-fields input::placeholder {
    color: rgb(23 18 29 / 0.55);
}

.input-fields input:focus {
    outline: 2px solid rgb(23 18 29 / 0.65);
    outline-offset: 2px;
    background: white;
}

/* Signup buttons */

#buttons-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    margin-top: 1.5rem;
}

.signUp-buttons {
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    gap: 0.75rem; 
    width: min(100%, 22rem);
}

.signUp-buttons button { width: 100%; 
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

.signUp-buttons button:hover,
.signUp-buttons button:focus-visible {
    background-color: rgb(84, 43, 117);
    transform: translateY(-2px);
}

/* Signup message */

#buttons-section p {
    margin: 0.75rem 0 0;
    color: inherit;
    text-align: center;
    max-width: 28rem;
}

/* Bottom buttons outside the form */

.signup-page > button {
    display: block; 
    width: min(100%, 22rem); 
    min-height: 2.75rem; 
    margin: 0.75rem auto 0; 
    padding: 0.7rem 1rem; 
    box-sizing: border-box; 
    border: 1px solid white; 
    border-radius: 0.4rem; 
    color: white; 
    background-color: rgb(108, 61, 145); 
    font: inherit; 
    font-weight: 700; 
    cursor: pointer; 
    transition: background-color 180ms ease, transform 180ms ease;
}

.signup-page > button:hover,
.signup-page > button:focus-visible {
    background-color: rgb(84, 43, 117);
    transform: translateY(-2px);
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
        font-size: clamp(2.75rem, 9vw, 3.75rem);
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

</style>

