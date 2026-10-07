<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppNavbar from './components/AppNavbar.vue';
import AppToast from './components/AppToast.vue';
import { usingMockBookings } from './features/booking/bookingService.js';
import { isLoading } from './services/loading.js';

/* The landing, login and sign-up screens are full-bleed designs of their own, so
   they opt out of the application chrome via `meta.chrome: false`. */
const route = useRoute();
const showChrome = computed(() => route.meta.chrome !== false);
</script>

<template>
    <div class="shell">
        <p v-if="usingMockBookings" class="dev-banner">
            Development mode — the booking screens are running on mock data. Nothing here is a
            real booking.
        </p>

        <AppNavbar v-if="showChrome" />

        <main>
            <RouterView />
        </main>

        <AppToast />

        <div v-if="isLoading" class="app-loading" aria-busy="true" aria-live="polite">
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

    --ps-bg: #f6f5f8;
    --ps-surface: #ffffff;
    --ps-border: #dcd8e2;
    --ps-border-soft: #e9e5ee;
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

    --ps-radius: 10px;
    --ps-radius-sm: 6px;
}

body {
    margin: 0;
    background: var(--ps-bg);
    color: var(--ps-text);
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
    font-size: 15px;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
}

.shell {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.shell > main {
    flex: 1;
}

.dev-banner {
    margin: 0;
    padding: 0.4rem 1rem;
    text-align: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ps-warn);
    background: var(--ps-warn-bg);
    border-bottom: 1px solid #f0d5ab;
}

/* Shared layout and control classes used by the booking screens. */
.page {
    max-width: 1120px;
    margin: 0 auto;
    padding: 1.5rem 1rem 4rem;
}

.card {
    background: var(--ps-surface);
    border: 1px solid var(--ps-border-soft);
    border-radius: var(--ps-radius);
    box-shadow: 0 1px 2px rgba(24, 18, 33, 0.05), 0 1px 3px rgba(24, 18, 33, 0.07);
    padding: 1.1rem;
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
    padding: 0.55rem 0.9rem;
    font: inherit;
    font-weight: 600;
    cursor: pointer;
    background: var(--ps-brand);
    color: #fff;
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.12s ease;
}

.btn:hover:not(:disabled) {
    background: var(--ps-brand-dark);
}

.btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.btn-secondary {
    background: var(--ps-surface);
    color: var(--ps-text);
    border-color: var(--ps-border);
}

.btn-secondary:hover:not(:disabled) {
    background: #f2eff5;
}

.btn-danger {
    background: var(--ps-danger);
}

.btn-danger:hover:not(:disabled) {
    background: #8f1e17;
}

.btn-sm {
    padding: 0.35rem 0.6rem;
    min-height: 32px;
    font-size: 0.85rem;
}

input[type='date'],
input[type='text'],
input[type='email'],
input[type='password'],
select {
    width: 100%;
    padding: 0.55rem 0.65rem;
    font: inherit;
    color: var(--ps-text);
    background: var(--ps-surface);
    border: 1px solid var(--ps-border);
    border-radius: var(--ps-radius-sm);
    min-height: 40px;
}

input:focus-visible,
select:focus-visible,
button:focus-visible,
a:focus-visible {
    outline: 3px solid var(--ps-brand-light);
    outline-offset: 1px;
}

.alert {
    padding: 0.7rem 0.85rem;
    border-radius: var(--ps-radius-sm);
    border: 1px solid;
    font-size: 0.9rem;
    margin: 0 0 0.75rem;
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
    display: inline-block;
    padding: 0.12rem 0.5rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.02em;
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
    background: rgba(23, 18, 29, 0.55);
    pointer-events: all;
}

.app-loading__panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.85rem;
    padding: 1.1rem 1.25rem;
    background: rgba(255, 255, 255, 0.92);
    color: var(--ps-text);
    border-radius: var(--ps-radius);
    border: 1px solid var(--ps-border-soft);
    box-shadow: 0 6px 20px rgba(24, 18, 33, 0.2);
    min-width: 180px;
}

.app-loading__text {
    margin: 0;
    font-weight: 700;
}

.app-loading__spinner {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    border: 4px solid rgba(108, 62, 148, 0.25);
    border-top-color: var(--ps-brand);
    animation: ps-spin 0.9s linear infinite;
}

@keyframes ps-spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
