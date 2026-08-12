<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

import {
    registerUser,
    loginUser,
    logoutUser,
} from '../features/auth/authService';
import { getCurrentUser } from '../services/api.js';

const router = useRouter();

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
        message.value = JSON.stringify(result);
    } catch (error) {
        message.value = error.message;
    }
}
</script>

<template>
    <main>
        <h1>PukSquash Authentication Test</h1>

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

        <p>{{ message }}</p>

        <button @click="testBackend">
            Test Laravel Authentication
        </button>

        <button @click="router.push('/')">
            Back to home
        </button>
    </main>
</template>