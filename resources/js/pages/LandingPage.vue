<script setup>
/**
 * Route wrapper for the landing screen.
 *
 * HomePage.vue owns the look of the landing screen and communicates by events.
 * This wrapper turns those events into navigation, so HomePage did not have to
 * change when routing was introduced.
 */
import { useRouter } from 'vue-router';
import HomePage from './HomePage.vue';

const router = useRouter();
</script>

<template>
    <HomePage
        @sign-in="router.push({ name: 'login' })"
        @sign-up="router.push({ name: 'signup' })"
    />
</template>
