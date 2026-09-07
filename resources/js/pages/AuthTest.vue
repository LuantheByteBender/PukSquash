<script setup>
import { ref } from 'vue';

import {
    registerUser,
    loginUser,
    logoutUser,
} from '../features/auth/authService';

import {
    getCurrentUser,
    getCourts,
} from '../services/api.js';

const emit = defineEmits(['go-home']);

const email = ref('');
const password = ref('');
const message = ref('');

async function register() {
    try {
        const result = await registerUser(
            email.value,
            password.value
        );

        message.value =
            `Account created for ${result.user.email}. Check your email for the verification link.`;
    } catch (error) {
        message.value = error.message;
    }
}

async function login() {
    try {
        const result = await loginUser(
            email.value,
            password.value
        );

        if (!result.user.emailVerified) {
            message.value =
                'Your email is not verified yet. Please check your inbox.';
            return;
        }

        message.value =
            `Logged in successfully as ${result.user.email}`;
    } catch (error) {
        message.value = error.message;
    }
}

async function logout() {
    try {
        await logoutUser();
        message.value = 'Logged out successfully.';
    } catch (error) {
        message.value = error.message;
    }
}

async function testBackend() {
    try {
        const result = await getCurrentUser();

        message.value = JSON.stringify(
            result,
            null,
            2
        );
    } catch (error) {
        message.value = error.message;
    }
}

async function testCourts() {
    try {
        const courts = await getCourts();

        message.value = JSON.stringify(
            courts,
            null,
            2
        );
    } catch (error) {
        message.value = error.message;
    }
}
</script>

<template>
    <main>
        <h1>PukSquash Authentication Test</h1>

        <button @click="emit('go-home')">
            Go Home
        </button>

        <div>
            <input
                v-model="email"
                type="email"
                placeholder="NWU email"
            >
        </div>

        <div>
            <input
                v-model="password"
                type="password"
                placeholder="Password"
            >
        </div>

        <div>
            <button @click="register">
                Register
            </button>

            <button @click="login">
                Login
            </button>

            <button @click="logout">
                Logout
            </button>
        </div>

        <div>
            <button @click="testBackend">
                Test Laravel Authentication
            </button>

            <button @click="testCourts">
                Load Courts Through Laravel
            </button>
        </div>

        <pre>{{ message }}</pre>
    </main>
</template>