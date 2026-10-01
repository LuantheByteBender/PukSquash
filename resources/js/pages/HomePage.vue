<script setup>
// The parent component listens for these events and decides which page to show.
const emit = defineEmits(['sign-in', 'sign-up']);
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
    background: var(--ps-brand-gradient);
    position: relative;
    overflow: hidden;
}

.landing-page::before,
.landing-page::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    animation: home-orb 8s ease-in-out infinite alternate;
}

.landing-page::before {
    width: 400px;
    height: 400px;
    top: -12%;
    right: -10%;
    background: rgba(255, 255, 255, 0.04);
}

.landing-page::after {
    width: 280px;
    height: 280px;
    bottom: -8%;
    left: -6%;
    background: rgba(255, 255, 255, 0.03);
    animation-delay: -4s;
}

@keyframes home-orb {
    from { transform: translate(0, 0) scale(1); }
    to { transform: translate(20px, -15px) scale(1.1); }
}

.hero {
    position: relative;
    z-index: 1;
    animation: hero-in 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes hero-in {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.hero h1 {
    margin-bottom: 1rem;
    font-size: clamp(3.5rem, 12vw, 8rem);
    font-weight: 900;
    letter-spacing: -0.05em;
    text-shadow: 0 4px 24px rgba(0, 0, 0, 0.12);
}

.hero p {
    margin-bottom: 2.5rem;
    font-size: 1.2rem;
    color: rgba(255, 255, 255, 0.75);
    font-weight: 500;
    max-width: 28rem;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.5;
}

.actions {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.button {
    padding: 0.85rem 1.85rem;
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 0.75rem;
    font-size: 0.95rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.button-primary {
    color: var(--ps-brand-dark);
    background: white;
    border-color: white;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.button-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
}

.button-primary:active {
    transform: translateY(0) scale(0.97);
}

.button-secondary {
    color: white;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(4px);
}

.button-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.7);
    transform: translateY(-2px);
}

.button-secondary:active {
    transform: translateY(0) scale(0.97);
}
</style>
