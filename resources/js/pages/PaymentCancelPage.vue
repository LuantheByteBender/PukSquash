<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const message = ref('Your payment was cancelled. You can try again.');
</script>

<template>
    <main class="cancel-page">
        <section class="card">
            <h1>Payment Cancelled</h1>
            <p>{{ message }}</p>
            <button type="button" @click="router.push({ name: 'registration-payment' })">
                Try again
            </button>
        </section>
    </main>
</template>

<style scoped>
.cancel-page {
    min-height: 100svh;
    display: grid;
    place-items: center;
    padding: 2rem 1.25rem;
    background: #f3f4f6;
}

.card {
    width: min(520px, 100%);
    background: white;
    border-radius: 14px;
    padding: 1.5rem;
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

h1 {
    margin: 0 0 0.5rem;
    font-size: 1.5rem;
    font-weight: 900;
}

p {
    margin: 0 0 1rem;
    color: #374151;
}

button {
    padding: 0.75rem 0.9rem;
    border-radius: 10px;
    border: none;
    background: #6c3e94;
    color: white;
    font-weight: 800;
    cursor: pointer;
}
</style>
