<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const phase = ref('loading');

onMounted(() => {
    setTimeout(() => {
        phase.value = 'welcome';
    }, 1400);

    setTimeout(() => {
        phase.value = 'exit';
    }, 2800);

    setTimeout(() => {
        router.push({ name: 'login' });
    }, 3400);
});
</script>

<template>
    <div class="splash" :class="`splash--${phase}`">
        <div class="splash__bg" aria-hidden="true">
            <div class="splash__orbs">
                <span class="splash__orb splash__orb--1"></span>
                <span class="splash__orb splash__orb--2"></span>
                <span class="splash__orb splash__orb--3"></span>
                <span class="splash__orb splash__orb--4"></span>
                <span class="splash__orb splash__orb--5"></span>
            </div>
        </div>

        <div class="splash__content">
            <div class="splash__logo-ring">
                <span class="splash__logo-letter">P</span>
            </div>

            <Transition name="title-fade" mode="out-in">
                <h1 v-if="phase === 'loading'" key="brand" class="splash__title">
                    PukSquash
                </h1>
                <h1 v-else key="welcome" class="splash__title splash__title--welcome">
                    Welcome
                </h1>
            </Transition>

            <div class="splash__loader" :class="{ 'splash__loader--done': phase !== 'loading' }">
                <div class="splash__loader-track">
                    <div class="splash__loader-bar" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.splash {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: grid;
    place-items: center;
    transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.splash--exit {
    opacity: 0;
    pointer-events: none;
}

.splash__bg {
    position: absolute;
    inset: 0;
    background: var(--ps-brand-gradient);
    background-size: 200% 200%;
    animation: splash-gradient 10s ease-in-out infinite;
    overflow: hidden;
}

@keyframes splash-gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.splash__orbs {
    position: absolute;
    inset: 0;
}

.splash__orb {
    position: absolute;
    border-radius: 999px;
    opacity: 0.5;
    mix-blend-mode: screen;
    filter: blur(0.25px);
    animation: orb-float 7s ease-in-out infinite alternate;
}

.splash__orb--1 {
    width: 240px;
    height: 240px;
    top: -8%;
    left: -6%;
    background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0) 70%);
    animation-duration: 8.5s;
}

.splash__orb--2 {
    width: 180px;
    height: 180px;
    bottom: -10%;
    right: 10%;
    background: radial-gradient(circle at 35% 35%, rgba(216, 196, 238, 0.22), rgba(216, 196, 238, 0) 72%);
    animation-duration: 7.8s;
    animation-delay: -2.2s;
}

.splash__orb--3 {
    width: 120px;
    height: 120px;
    top: 18%;
    right: 14%;
    background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, 0.18), rgba(255, 255, 255, 0) 70%);
    animation-duration: 6.8s;
    animation-delay: -4.1s;
}

.splash__orb--4 {
    width: 140px;
    height: 140px;
    bottom: 22%;
    left: 12%;
    background: radial-gradient(circle at 35% 35%, rgba(216, 196, 238, 0.16), rgba(216, 196, 238, 0) 72%);
    animation-duration: 7.2s;
    animation-delay: -3.1s;
}

.splash__orb--5 {
    width: 90px;
    height: 90px;
    top: 62%;
    right: 28%;
    background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, 0.16), rgba(255, 255, 255, 0) 70%);
    animation-duration: 6.4s;
    animation-delay: -1.6s;
}

@keyframes orb-float {
    from { transform: translate(0, 0) scale(1); }
    to { transform: translate(18px, -14px) scale(1.08); }
}

.splash__bg::before,
.splash__bg::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    animation: splash-orb 6s ease-in-out infinite alternate;
}

.splash__bg::before {
    width: 340px;
    height: 340px;
    top: -15%;
    right: -8%;
    background: rgba(255, 255, 255, 0.04);
}

.splash__bg::after {
    width: 220px;
    height: 220px;
    bottom: -10%;
    left: -5%;
    background: rgba(255, 255, 255, 0.03);
    animation-delay: -3s;
}

@keyframes splash-orb {
    from { transform: translate(0, 0) scale(1); }
    to { transform: translate(15px, -10px) scale(1.08); }
}

.splash__content {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.75rem;
    animation: splash-enter 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes splash-enter {
    from {
        opacity: 0;
        transform: scale(0.88) translateY(16px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.splash__logo-ring {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    border: 3px solid rgba(255, 255, 255, 0.25);
    display: grid;
    place-items: center;
    animation: ring-pulse 2s ease-in-out infinite;
    box-shadow: 0 0 40px rgba(255, 255, 255, 0.08);
}

@keyframes ring-pulse {
    0%, 100% { transform: scale(1); border-color: rgba(255,255,255,0.25); box-shadow: 0 0 40px rgba(255, 255, 255, 0.08); }
    50% { transform: scale(1.06); border-color: rgba(255,255,255,0.5); box-shadow: 0 0 60px rgba(255, 255, 255, 0.15); }
}

.splash__logo-letter {
    font-size: 2.5rem;
    font-weight: 900;
    color: #fff;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
}

.splash__title {
    margin: 0;
    font-size: clamp(2.2rem, 7vw, 3.2rem);
    font-weight: 900;
    color: #fff;
    letter-spacing: -0.03em;
    text-align: center;
    text-shadow: 0 2px 16px rgba(0, 0, 0, 0.1);
}

.splash__title--welcome {
    background: linear-gradient(135deg, #fff 0%, #d8c4ee 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.splash__loader {
    width: 200px;
    opacity: 1;
    transition: opacity 0.4s ease;
}

.splash__loader--done {
    opacity: 0;
}

.splash__loader-track {
    height: 3px;
    border-radius: 2px;
    background: rgba(255, 255, 255, 0.15);
    overflow: hidden;
}

.splash__loader-bar {
    height: 100%;
    border-radius: 2px;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.6) 0%, rgba(255, 255, 255, 0.9) 100%);
    animation: loader-progress 1.4s ease-out forwards;
}

@keyframes loader-progress {
    from { width: 0; }
    to { width: 100%; }
}

.title-fade-enter-active {
    transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.title-fade-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}

.title-fade-enter-from {
    opacity: 0;
    transform: translateY(12px) scale(0.95);
}

.title-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>
