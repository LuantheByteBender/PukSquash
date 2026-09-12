<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AppNavbar from './components/AppNavbar.vue';
import AppToast from './components/AppToast.vue';

/* The landing, login and sign-up screens are full-bleed designs of their own, so
   they opt out of the application chrome via `meta.chrome: false`. */
const route = useRoute();
const showChrome = computed(() => route.meta.chrome !== false);
</script>

<template>
    <div class="shell">
        <AppNavbar v-if="showChrome" />

        <main>
            <RouterView />
        </main>

        <AppToast />
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
       purple used by navigation and buttons. */
    --ps-available: #ffffff;
    --ps-available-bd: #b3d9be;
    --ps-available-ink: #1d6b34;

    --ps-open: #eaf2ff;
    --ps-open-bd: #9dc4f0;
    --ps-open-ink: #114b86;

    --ps-full: #eeeef1;
    --ps-full-bd: #c9c9d2;
    --ps-full-ink: #4f4d59;

    --ps-locked: #fbeef8;
    --ps-locked-bd: #ddb0d4;
    --ps-locked-ink: #7a2f6d;

    --ps-coach: #fff4e5;
    --ps-coach-bd: #f0c48a;
    --ps-coach-ink: #8a5209;

    --ps-inactive: #f3f2f5;
    --ps-inactive-bd: #e0dce6;
    --ps-inactive-ink: #9b95a4;

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
</style>
