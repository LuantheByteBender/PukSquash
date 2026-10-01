<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useRouter } from 'vue-router';
import AppNavbar from './components/AppNavbar.vue';
import AppToast from './components/AppToast.vue';
import { usingMockBookings } from './features/booking/bookingService.js';
import { useAuth } from './composables/useAuth.js';

/* The landing, login and sign-up screens are full-bleed designs of their own, so
   they opt out of the application chrome via `meta.chrome: false`. */
const route = useRoute();
const router = useRouter();
const showChrome = computed(() => route.meta.chrome !== false);

const booting = ref(true);

function waitForTruthy(source) {
    if (source.value) {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        let stop = () => {};
        const timeout = setTimeout(() => {
            stop();
            console.warn('[PukSquash] Timed out waiting for auth readiness during boot.');
            resolve();
        }, 7000);

        stop = watch(source, (value) => {
            if (!value) return;
            clearTimeout(timeout);
            stop();
            resolve();
        });
    });
}

onMounted(async () => {
    const { ready } = useAuth();
    await waitForTruthy(ready);
    await router.isReady();
    booting.value = false;
});
</script>

<template>
    <div class="shell">
        <p v-if="usingMockBookings" class="dev-banner">
            Development mode — the booking screens are running on mock data. Nothing here is a
            real booking.
        </p>

        <AppNavbar v-if="showChrome && !booting" />

        <main v-if="!booting || !showChrome">
            <RouterView v-slot="{ Component }">
                <Transition name="page-fade" mode="out-in">
                    <component :is="Component" />
                </Transition>
            </RouterView>
        </main>

        <AppToast />

        <div
            v-if="booting && showChrome"
            class="app-loading app-loading--boot"
            aria-busy="true"
            aria-live="polite"
        >
            <div class="app-loading__panel">
                <div class="app-loading__spinner" aria-hidden="true" />
                <p class="app-loading__text">Loading…</p>
            </div>
        </div>
    </div>
</template>

<style>
/* Application-wide design tokens.
   Deliberately unscoped: these custom properties are the shared vocabulary the
   booking components draw on.

   The palette is NWU purple, taken from the approved wireframes in the Functional
   Specifications. Note that HomePage.vue is still green — that file belongs to
   another part of the team, so the landing page and the booking screens will not
   match until it is updated too. */
:root {
    /* NWU purple, sampled from the logo in the approved wireframes (#6C3E94). */
    --ps-brand: #6c3e94;
    --ps-brand-dark: #56306f;
    --ps-brand-light: #8b67ab;
    --ps-brand-tint: #f2ecf7;
    --ps-brand-glow: rgba(108, 62, 148, 0.18);

    --ps-brand-gradient: linear-gradient(135deg, #7b4fa6 0%, #6C3D91 30%, #512d6e 70%, #3d2154 100%);
    --ps-brand-gradient-hover: linear-gradient(135deg, #8b5fb6 0%, #7b4fa6 30%, #6C3D91 70%, #512d6e 100%);
    --ps-bg-gradient: linear-gradient(135deg, rgba(108, 61, 145, 0.12) 0%, rgba(81, 45, 110, 0.08) 50%, rgba(61, 33, 84, 0.06) 100%);

    --ps-bg: #f0eaf6;
    --ps-surface: #faf8fd;
    --ps-surface-elevated: #ffffff;
    --ps-border: #d5cee0;
    --ps-border-soft: #e4dded;
    --ps-text: #1c1922;
    --ps-text-muted: #5d5766;
    --ps-text-faint: #8b8595;

    /* Booking cell states. Six states need six distinguishable hues, and each
       cell also carries a text label — colour alone never carries the meaning.
       LOCKED is deliberately a pinker plum so it is not mistaken for the brand
       purple used by navigation and buttons.
       The fills are deliberately stronger than a pale tint so the states can be
       told apart at a glance in both the grid and the legend; every ink colour
       keeps at least 4.5:1 contrast on its fill. */
    --ps-available: #d7f0de;
    --ps-available-bd: #8cc79c;
    --ps-available-ink: #1a5e2e;

    --ps-open: #cfe2fb;
    --ps-open-bd: #7eaee4;
    --ps-open-ink: #0f437a;

    --ps-full: #d9d9e0;
    --ps-full-bd: #a9a9b6;
    --ps-full-ink: #3f3d48;

    --ps-locked: #f4d6ec;
    --ps-locked-bd: #cc8fc0;
    --ps-locked-ink: #6b2860;

    --ps-coach: #fde0b8;
    --ps-coach-bd: #e3a553;
    --ps-coach-ink: #774607;

    /* Grey family for cells that cannot be booked, split three ways so the
       reason is visible: the court is inactive, the slot has already started,
       or you already have a booking somewhere else at that hour. */
    --ps-inactive: #e4e1e8;
    --ps-inactive-bd: #c4bfcc;
    --ps-inactive-ink: #5f5968;

    --ps-past: #f0eef3;
    --ps-past-bd: #dcd8e2;
    --ps-past-ink: #6f6979;

    --ps-busy: #e6dcf2;
    --ps-busy-bd: #b39ccf;
    --ps-busy-ink: #4f2f6e;

    --ps-danger: #b3261e;
    --ps-danger-bg: #fdecea;
    --ps-success: #1d6b34;
    --ps-success-bg: #e8f5ec;
    --ps-warn: #8a5209;
    --ps-warn-bg: #fff4e5;

    --ps-radius: 12px;
    --ps-radius-sm: 8px;
    --ps-radius-lg: 16px;

    --ps-shadow-sm: 0 1px 3px rgba(108, 62, 148, 0.06), 0 1px 2px rgba(108, 62, 148, 0.04);
    --ps-shadow: 0 4px 12px rgba(108, 62, 148, 0.08), 0 2px 4px rgba(108, 62, 148, 0.04);
    --ps-shadow-lg: 0 8px 28px rgba(108, 62, 148, 0.12), 0 4px 8px rgba(108, 62, 148, 0.06);
    --ps-shadow-xl: 0 16px 48px rgba(108, 62, 148, 0.16), 0 6px 12px rgba(108, 62, 148, 0.08);

    --ps-sidebar-width: 280px;
    --ps-transition: 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    --ps-transition-fast: 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    --ps-transition-slow: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

*,
*::before,
*::after {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: var(--ps-bg);
    background-image: var(--ps-bg-gradient);
    color: var(--ps-text);
    font-family: 'Inter', 'Instrument Sans', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
    font-size: 15px;
    line-height: 1.6;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    overflow-x: hidden;
}

::selection {
    background: rgba(108, 62, 148, 0.2);
    color: var(--ps-text);
}

.shell {
    min-height: 100vh;
    position: relative;
    display: flex;
    flex-direction: column;
}

.shell::before {
    content: '';
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    opacity: 0.25;
    background-image:
        radial-gradient(ellipse 600px 600px at 15% 20%, rgba(108, 62, 148, 0.08) 0%, transparent 70%),
        radial-gradient(ellipse 500px 500px at 85% 30%, rgba(139, 103, 171, 0.06) 0%, transparent 70%),
        radial-gradient(ellipse 400px 400px at 50% 80%, rgba(108, 62, 148, 0.05) 0%, transparent 70%);
    animation: ps-bg-drift 30s ease-in-out infinite alternate;
}

.shell::after {
    content: '';
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    opacity: 0.18;
    background-image:
        radial-gradient(1px 1px at 12% 18%, rgba(255, 255, 255, 0.55) 0, rgba(255, 255, 255, 0) 60%),
        radial-gradient(1px 1px at 78% 26%, rgba(255, 255, 255, 0.45) 0, rgba(255, 255, 255, 0) 60%),
        radial-gradient(1px 1px at 64% 82%, rgba(255, 255, 255, 0.4) 0, rgba(255, 255, 255, 0) 60%),
        radial-gradient(1px 1px at 28% 74%, rgba(255, 255, 255, 0.35) 0, rgba(255, 255, 255, 0) 60%),
        radial-gradient(2px 2px at 42% 36%, rgba(255, 255, 255, 0.22) 0, rgba(255, 255, 255, 0) 55%),
        radial-gradient(2px 2px at 86% 66%, rgba(255, 255, 255, 0.18) 0, rgba(255, 255, 255, 0) 55%);
    background-repeat: repeat;
    background-size: 420px 420px;
    filter: blur(0.2px);
    animation: ps-stars-drift 22s linear infinite;
}

.shell > * {
    position: relative;
    z-index: 1;
}

.shell > main {
    flex: 1;
}

.dev-banner {
    margin: 0;
    padding: 0.45rem 1rem;
    text-align: center;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ps-warn);
    background: var(--ps-warn-bg);
    border-bottom: 1px solid #f0d5ab;
    letter-spacing: 0.01em;
}

/* Shared layout and control classes used by the booking screens. */
.page {
    max-width: 1120px;
    margin: 0 auto;
    padding: 1.5rem 1.25rem 4rem;
}

.card {
    background: var(--ps-surface);
    border: 1px solid rgba(224, 214, 235, 0.7);
    border-radius: var(--ps-radius);
    box-shadow: var(--ps-shadow-sm);
    padding: 1.25rem;
    transition: box-shadow var(--ps-transition), transform var(--ps-transition), border-color var(--ps-transition);
}

.card:hover {
    box-shadow: var(--ps-shadow);
    border-color: rgba(224, 214, 235, 1);
}

.row {
    display: flex;
    gap: 0.6rem;
    align-items: center;
    flex-wrap: wrap;
}

.muted {
    color: var(--ps-text-muted);
}

.faint {
    color: var(--ps-text-faint);
    font-size: 0.85rem;
}

.btn {
    appearance: none;
    border: 1px solid transparent;
    border-radius: var(--ps-radius-sm);
    padding: 0.55rem 1rem;
    font: inherit;
    font-weight: 600;
    font-size: 0.875rem;
    letter-spacing: 0.01em;
    cursor: pointer;
    background: var(--ps-brand);
    color: #fff;
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    transition: all var(--ps-transition);
    position: relative;
    overflow: hidden;
}

.btn::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 60%);
    opacity: 0;
    transition: opacity var(--ps-transition-fast);
}

.btn:hover:not(:disabled)::after {
    opacity: 1;
}

.btn:hover:not(:disabled) {
    background: var(--ps-brand-dark);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 62, 148, 0.25);
}

.btn:active:not(:disabled) {
    transform: translateY(0) scale(0.98);
    box-shadow: none;
}

.btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.btn-secondary {
    background: var(--ps-surface);
    color: var(--ps-text);
    border-color: #d4c9e2;
}

.btn-secondary::after {
    display: none;
}

.btn-secondary:hover:not(:disabled) {
    background: var(--ps-brand-tint);
    border-color: var(--ps-brand-light);
    box-shadow: 0 2px 8px rgba(108, 62, 148, 0.1);
}

.btn-danger {
    background: var(--ps-danger);
}

.btn-danger:hover:not(:disabled) {
    background: #8f1e17;
    box-shadow: 0 4px 12px rgba(179, 38, 30, 0.25);
}

.btn-sm {
    padding: 0.35rem 0.65rem;
    min-height: 32px;
    font-size: 0.82rem;
    border-radius: var(--ps-radius-sm);
}

input[type='date'],
input[type='text'],
input[type='email'],
input[type='password'],
select {
    width: 100%;
    padding: 0.6rem 0.75rem;
    font: inherit;
    font-size: 0.9rem;
    color: var(--ps-text);
    background: var(--ps-surface);
    border: 1.5px solid #d4c9e2;
    border-radius: var(--ps-radius-sm);
    min-height: 42px;
    transition: border-color var(--ps-transition), box-shadow var(--ps-transition), background var(--ps-transition);
}

input[type='date']:focus,
input[type='text']:focus,
input[type='email']:focus,
input[type='password']:focus,
select:focus {
    border-color: var(--ps-brand);
    box-shadow: 0 0 0 3px var(--ps-brand-glow);
    background: #fff;
    outline: none;
}

input:focus-visible,
select:focus-visible,
button:focus-visible,
a:focus-visible {
    outline: 3px solid var(--ps-brand-light);
    outline-offset: 2px;
}

input::placeholder {
    color: var(--ps-text-faint);
}

.alert {
    padding: 0.75rem 1rem;
    border-radius: var(--ps-radius-sm);
    border: 1px solid;
    font-size: 0.875rem;
    font-weight: 500;
    margin: 0 0 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-error {
    background: var(--ps-danger-bg);
    border-color: #f3c6c2;
    color: var(--ps-danger);
}

.alert-warn {
    background: var(--ps-warn-bg);
    border-color: #f0d5ab;
    color: var(--ps-warn);
}

.pill {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.55rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    background: var(--ps-brand-tint);
    color: var(--ps-brand);
    border: 1px solid #ddcfe9;
}

.pill-grey {
    background: #f2eff5;
    color: var(--ps-text-muted);
    border-color: var(--ps-border);
}

.pill-blue {
    background: var(--ps-open);
    color: var(--ps-open-ink);
    border-color: var(--ps-open-bd);
}

.pill-amber {
    background: var(--ps-warn-bg);
    color: var(--ps-warn);
    border-color: #f0d5ab;
}

.pill-red {
    background: var(--ps-danger-bg);
    color: var(--ps-danger);
    border-color: #f3c6c2;
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0 0 0 0);
    white-space: nowrap;
    border: 0;
}

.app-loading {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(23, 18, 29, 0.6);
    backdrop-filter: blur(4px);
    pointer-events: all;
    animation: ps-fade-in 0.2s ease;
}

.app-loading--boot {
    background: radial-gradient(1200px 800px at 50% 35%, rgba(108, 62, 148, 0.22) 0%, rgba(18, 8, 32, 0.98) 55%, rgba(10, 4, 20, 0.99) 100%);
    backdrop-filter: none;
}

.app-loading__panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 2rem;
    background: rgba(255, 255, 255, 0.95);
    color: var(--ps-text);
    border-radius: var(--ps-radius-lg);
    border: 1px solid var(--ps-border-soft);
    box-shadow: var(--ps-shadow-xl);
    min-width: 200px;
    animation: ps-scale-in 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.app-loading__text {
    margin: 0;
    font-weight: 700;
    font-size: 0.9rem;
    letter-spacing: 0.01em;
}

.app-loading__spinner {
    width: 40px;
    height: 40px;
    border-radius: 999px;
    border: 3.5px solid rgba(108, 62, 148, 0.15);
    border-top-color: var(--ps-brand);
    animation: ps-spin 0.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

@keyframes ps-spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes ps-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes ps-scale-in {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes ps-bg-drift {
    0% { transform: translate3d(0, 0, 0) scale(1); }
    50% { transform: translate3d(-30px, 20px, 0) scale(1.02); }
    100% { transform: translate3d(20px, -10px, 0) scale(1); }
}

@keyframes ps-stars-drift {
    from {
        transform: translate3d(0, 0, 0);
    }
    to {
        transform: translate3d(-120px, 140px, 0);
    }
}

@keyframes ps-shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

/* ── Route transition ─────────────────────────── */
.page-fade-enter-active {
    transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-fade-leave-active {
    transition: opacity 0.15s cubic-bezier(0.4, 0, 0.2, 1), transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.page-fade-leave-to {
    opacity: 0;
    transform: translateY(-4px);
}

/* ── Reduced motion ───────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }

    .shell::before,
    .shell::after {
        animation: none;
    }
}

/* ── Scrollbar styling ────────────────────────── */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(108, 62, 148, 0.18);
    border-radius: 999px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(108, 62, 148, 0.3);
}
</style>
