<script setup>
// The parent component listens for these events and decides which page to show.
const emit = defineEmits(['sign-in', 'sign-up', 'auth-test']);
</script>

<template>
    <main class="landing-page">
        <section class="hero">
            <h1>PukSquash</h1>

            <p>Book courts, manage matches, and enjoy squash.</p>

            <div class="actions">
                <button
                    class="button button-primary"
                    @click="emit('sign-in')"
                >
                    Sign in
                </button>

                <button
                    class="button button-secondary"
                    @click="emit('sign-up')"
                >
                    Sign up
                </button>

                <button
                    class="button button-secondary"
                    @click="emit('auth-test')"
                >
                    Auth test
                </button>
            </div>
        </section>
    </main>
</template>

<style scoped>
.landing-page {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 2rem;
    color: white;
    text-align: center;
    background:
        linear-gradient(rgba(10, 35, 25, 0.8), rgba(10, 35, 25, 0.9)),
        #14532d;
}

.hero h1 {
    margin-bottom: 1rem;
    font-size: clamp(4rem, 12vw, 9rem);
    font-weight: 900;
    letter-spacing: -0.05em;
}

.hero p {
    margin-bottom: 2rem;
    font-size: 1.25rem;
    color: #dcfce7;
}

.actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.button {
    padding: 0.9rem 1.75rem;
    border: 2px solid white;
    border-radius: 0.75rem;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
}

.button-primary {
    color: #14532d;
    background: white;
}

.button-secondary {
    color: white;
    background: transparent;
}
</style>
