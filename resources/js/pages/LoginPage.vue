<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();
</script>

<template>
    <main>
        <h1>Sign in</h1>
        <p>The sign-in form will go here.</p>

        <button @click="router.push('/')">
            Back to home
        </button>
    </main>
</template>