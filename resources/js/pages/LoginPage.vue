<script setup>
// Allows this page to ask App.vue to return to the landing page.
const emit = defineEmits(['go-home']);
</script>

<template>
    <main>
        <h1>Sign in</h1>
        <p>The sign-in form will go here.</p>

        <button @click="emit('go-home')">
            Back to home
        </button>
    </main>
</template>