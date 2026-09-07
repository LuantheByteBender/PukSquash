<script setup>
import { ref } from 'vue';

import HomePage from './pages/HomePage.vue';
import LoginPage from './pages/LoginPage.vue';
import SignUpPage from './pages/SignUpPage.vue';
import AuthTest from './pages/AuthTest.vue';
import { myBookings } from '@firebasegen/example-connector';

// Vue shows the landing page when the application first starts.
// The buttons on HomePage update this value to select another page.
const currentPage = ref('home');
</script>

<template>
    <HomePage
        v-if="currentPage === 'home'"
        @sign-in="currentPage = 'login'"
        @sign-up="currentPage = 'signup'"
        @auth-test="currentPage = 'auth-test'"
    />

    <LoginPage
        v-else-if="currentPage === 'login'"
        @go-home="currentPage = 'home'"
    />

    <SignUpPage
        v-else-if="currentPage === 'signup'"
        @go-home="currentPage = 'home'"
    />

     <AuthTest
        v-else-if="currentPage === 'auth-test'"
        @go-home="currentPage = 'home'"
    />
</template>