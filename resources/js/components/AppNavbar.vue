<script setup>
import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';
import { useAccount, refreshAccount, clearAccount } from '../composables/useAccount.js';

const router = useRouter();
const route = useRoute();
const { isSignedIn, displayName } = useAuth();
const { role } = useAccount();

const open = ref(false);
const confirmingSignOut = ref(false);

const userInitials = computed(() => {
    const name = displayName.value || '';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return 'U';
});

watch(isSignedIn, async (signedIn) => {
    if (!signedIn) {
        clearAccount();
        open.value = false;
        return;
    }

    try {
        await refreshAccount();
    } catch (error) {
        if (error?.status === 401 || error?.status === 403) {
            await signOut();
        }
    }
}, { immediate: true });

watch(() => route.fullPath, () => {
    open.value = false;
});

function toggleSidebar() {
    open.value = !open.value;
}

function requestSignOut() {
    open.value = false;
    confirmingSignOut.value = true;
}

async function signOut() {
    confirmingSignOut.value = false;

    try {
        // Loaded on demand: authService imports services/firebase.js, which
        // throws at module load when Firebase is not configured yet.
        const { logoutUser } = await import('../features/auth/authService.js');
        await logoutUser();
    } catch {
        // Already signed out, or Firebase is unavailable — either way, leave.
    }

    clearAccount();
    router.push({ name: 'login' });
}
</script>

<template>
    <header class="nav">
        <div class="nav-bar">
            <RouterLink :to="{ name: isSignedIn ? 'book' : 'login' }" class="brand" @click="open = false">
                <span class="brand-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" stroke="white" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="8" rx="5" ry="7"></ellipse>
                        <line x1="12" y1="1" x2="12" y2="15"></line>
                        <line x1="7" y1="8" x2="17" y2="8"></line>
                        <line x1="12" y1="15" x2="12" y2="22"></line>
                    </svg>
                </span>
                <span class="brand-text">PukSquash</span>
            </RouterLink>

            <div class="nav-spacer"></div>

            <template v-if="isSignedIn">
                <button
                    class="hamburger"
                    type="button"
                    :aria-expanded="open"
                    aria-label="Toggle navigation menu"
                    @click="toggleSidebar"
                >
                    <span class="hamburger-box">
                        <span class="hamburger-line" :class="{ 'hamburger-line--active': open }"></span>
                        <span class="hamburger-line" :class="{ 'hamburger-line--active': open }"></span>
                        <span class="hamburger-line" :class="{ 'hamburger-line--active': open }"></span>
                    </span>
                </button>
            </template>

            <RouterLink v-else :to="{ name: 'login' }" class="signin-link">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                Sign in
            </RouterLink>
        </div>

        <Transition name="sidebar-overlay">
            <div v-if="open && isSignedIn" class="sidebar-overlay" @click="open = false"></div>
        </Transition>

        <Transition name="sidebar-slide">
            <nav v-if="open && isSignedIn" class="sidebar" role="navigation" aria-label="Main navigation">
                <div class="sidebar-header">
                    <div class="sidebar-user">
                        <div class="sidebar-avatar">{{ userInitials }}</div>
                        <div class="sidebar-user-info">
                            <p class="sidebar-user-name">{{ displayName || 'User' }}</p>
                            <span class="sidebar-user-role">{{ role || 'USER' }}</span>
                        </div>
                    </div>
                    <button class="sidebar-close" type="button" aria-label="Close menu" @click="open = false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg>
                    </button>
                </div>

                <div class="sidebar-body">
                    <div class="sidebar-section">
                        <p class="sidebar-section-label">Main</p>

                        <RouterLink :to="{ name: 'book' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                            <span>Book a court</span>
                        </RouterLink>

                        <RouterLink :to="{ name: 'my-bookings' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
                            <span>My bookings</span>
                        </RouterLink>

                        <RouterLink :to="{ name: 'coach-notice-board' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/></svg>
                            <span>Coach notice board</span>
                        </RouterLink>
                    </div>

                    <div v-if="role === 'COACH'" class="sidebar-section">
                        <p class="sidebar-section-label">Coaching</p>

                        <RouterLink :to="{ name: 'coach-bookings' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Coach availability</span>
                        </RouterLink>

                        <RouterLink :to="{ name: 'coach-team-practice' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span>Team practice booking</span>
                        </RouterLink>
                    </div>

                    <div v-if="role === 'ADMIN'" class="sidebar-section">
                        <p class="sidebar-section-label">Administration</p>

                        <RouterLink :to="{ name: 'admin-dashboard' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                            <span>Admin dashboard</span>
                        </RouterLink>

                        <RouterLink :to="{ name: 'admin-users' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
                            <span>Manage users</span>
                        </RouterLink>

                        <RouterLink :to="{ name: 'admin-courts' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            <span>Manage courts</span>
                        </RouterLink>
                    </div>

                    <div class="sidebar-section">
                        <p class="sidebar-section-label">Account</p>

                        <RouterLink :to="{ name: 'user-profile' }" class="sidebar-link" @click="open = false">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Profile</span>
                        </RouterLink>
                    </div>
                </div>

                <div class="sidebar-footer">
                    <button class="sidebar-signout" type="button" @click="requestSignOut">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        <span>Sign out</span>
                    </button>
                </div>
            </nav>
        </Transition>

        <Transition name="modal-fade">
            <div v-if="confirmingSignOut" class="backdrop" @click.self="confirmingSignOut = false">
                <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="signout-title">
                    <div class="dialog-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    </div>
                    <h2 id="signout-title">Sign out?</h2>
                    <p class="dialog-text">Are you sure you want to sign out of your account?</p>
                    <div class="dialog-actions">
                        <button class="btn btn-secondary" type="button" @click="confirmingSignOut = false">Cancel</button>
                        <button class="btn btn-danger" type="button" @click="signOut">Sign out</button>
                    </div>
                </div>
            </div>
        </Transition>
    </header>
</template>

<style scoped>
/* ── Top navigation bar ───────────────────────── */
.nav {
    position: sticky;
    top: 0;
    z-index: 40;
}

.nav-bar {
    background: var(--ps-brand-gradient);
    color: #fff;
    padding: 0 1.25rem;
    height: 56px;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 12px rgba(86, 48, 111, 0.2);
}

/* ── Brand ────────────────────────────────────── */
.brand {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    color: #fff;
    text-decoration: none;
    transition: opacity var(--ps-transition-fast);
}

.brand:hover { opacity: 0.85; }

.brand-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(4px);
    transition: background var(--ps-transition);
}

.brand:hover .brand-icon {
    background: rgba(255, 255, 255, 0.22);
}

.brand-text {
    font-weight: 800;
    font-size: 1.1rem;
    letter-spacing: -0.02em;
}

.nav-spacer { flex: 1; }

/* ── Hamburger button ─────────────────────────── */
.hamburger {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border: 0;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    cursor: pointer;
    transition: background var(--ps-transition);
    padding: 0;
}

.hamburger:hover {
    background: rgba(255, 255, 255, 0.2);
}

.hamburger-box {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    width: 20px;
    height: 16px;
    position: relative;
}

.hamburger-line {
    display: block;
    width: 18px;
    height: 2px;
    background: #fff;
    border-radius: 2px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: absolute;
}

.hamburger-line:nth-child(1) { top: 0; }
.hamburger-line:nth-child(2) { top: 50%; transform: translateY(-50%); }
.hamburger-line:nth-child(3) { bottom: 0; }

.hamburger-line--active:nth-child(1) {
    top: 50%;
    transform: translateY(-50%) rotate(45deg);
}
.hamburger-line--active:nth-child(2) {
    opacity: 0;
    transform: translateY(-50%) scaleX(0);
}
.hamburger-line--active:nth-child(3) {
    bottom: auto;
    top: 50%;
    transform: translateY(-50%) rotate(-45deg);
}

/* ── Sign in link ─────────────────────────────── */
.signin-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    padding: 0.45rem 0.85rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.1);
    transition: all var(--ps-transition);
}

.signin-link:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}

/* ── Sidebar overlay ──────────────────────────── */
.sidebar-overlay {
    position: fixed;
    inset: 0;
    z-index: 41;
    background: rgba(23, 18, 29, 0.5);
    backdrop-filter: blur(2px);
}

.sidebar-overlay-enter-active,
.sidebar-overlay-leave-active {
    transition: opacity 0.3s ease;
}
.sidebar-overlay-enter-from,
.sidebar-overlay-leave-to {
    opacity: 0;
}

/* ── Sidebar panel ────────────────────────────── */
.sidebar {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    z-index: 42;
    width: var(--ps-sidebar-width);
    max-width: 90vw;
    background: var(--ps-surface-elevated);
    box-shadow: -8px 0 32px rgba(86, 48, 111, 0.18);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    overscroll-behavior: contain;
}

.sidebar-slide-enter-active {
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.sidebar-slide-leave-active {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.6, 1);
}
.sidebar-slide-enter-from,
.sidebar-slide-leave-to {
    transform: translateX(100%);
}

/* ── Sidebar header ───────────────────────────── */
.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.25rem 1rem;
    border-bottom: 1px solid var(--ps-border-soft);
    background: linear-gradient(135deg, rgba(108, 62, 148, 0.04) 0%, rgba(108, 62, 148, 0.01) 100%);
}

.sidebar-user {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 0;
}

.sidebar-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: var(--ps-brand-gradient);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(108, 62, 148, 0.25);
}

.sidebar-user-info {
    min-width: 0;
}

.sidebar-user-name {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--ps-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sidebar-user-role {
    display: inline-block;
    margin-top: 0.15rem;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--ps-brand);
    background: var(--ps-brand-tint);
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
}

.sidebar-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: 0;
    background: transparent;
    color: var(--ps-text-muted);
    border-radius: 8px;
    cursor: pointer;
    transition: all var(--ps-transition-fast);
    flex-shrink: 0;
}

.sidebar-close:hover {
    background: var(--ps-brand-tint);
    color: var(--ps-brand);
}

/* ── Sidebar body ─────────────────────────────── */
.sidebar-body {
    flex: 1;
    padding: 0.5rem 0;
    overflow-y: auto;
}

.sidebar-section {
    padding: 0.5rem 0.75rem;
}

.sidebar-section-label {
    margin: 0 0 0.35rem 0.5rem;
    font-size: 0.65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--ps-text-faint);
}

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    padding: 0.6rem 0.75rem;
    border-radius: 10px;
    text-decoration: none;
    color: var(--ps-text);
    font-size: 0.875rem;
    font-weight: 600;
    transition: all var(--ps-transition-fast);
    position: relative;
}

.sidebar-link svg {
    color: var(--ps-text-muted);
    flex-shrink: 0;
    transition: color var(--ps-transition-fast);
}

.sidebar-link:hover {
    background: var(--ps-brand-tint);
    color: var(--ps-brand-dark);
}

.sidebar-link:hover svg {
    color: var(--ps-brand);
}

.sidebar-link.router-link-active {
    background: linear-gradient(135deg, rgba(108, 62, 148, 0.1) 0%, rgba(108, 62, 148, 0.06) 100%);
    color: var(--ps-brand-dark);
}

.sidebar-link.router-link-active svg {
    color: var(--ps-brand);
}

.sidebar-link.router-link-active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 60%;
    border-radius: 0 999px 999px 0;
    background: var(--ps-brand);
}

/* ── Sidebar footer ───────────────────────────── */
.sidebar-footer {
    padding: 0.75rem;
    border-top: 1px solid var(--ps-border-soft);
}

.sidebar-signout {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    width: 100%;
    padding: 0.6rem 0.75rem;
    border: 0;
    border-radius: 10px;
    background: transparent;
    color: var(--ps-danger);
    font: inherit;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--ps-transition-fast);
}

.sidebar-signout:hover {
    background: var(--ps-danger-bg);
}

/* ── Sign-out dialog ──────────────────────────── */
.backdrop {
    position: fixed;
    inset: 0;
    z-index: 60;
    padding: 1rem;
    background: rgba(23, 18, 29, 0.55);
    backdrop-filter: blur(4px);
    display: grid;
    place-items: center;
}

.modal-fade-enter-active { transition: opacity 0.2s ease; }
.modal-fade-leave-active { transition: opacity 0.15s ease; }
.modal-fade-enter-from,
.modal-fade-leave-to { opacity: 0; }

.dialog {
    max-width: 400px;
    width: 100%;
    background: var(--ps-surface-elevated);
    border-radius: var(--ps-radius-lg);
    padding: 1.5rem;
    box-shadow: var(--ps-shadow-xl);
    text-align: center;
}

.dialog-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: var(--ps-danger-bg);
    color: var(--ps-danger);
    margin-bottom: 0.75rem;
}

.dialog h2 {
    margin: 0 0 0.4rem;
    font-size: 1.1rem;
    font-weight: 700;
}

.dialog-text {
    margin: 0;
    color: var(--ps-text-muted);
    font-size: 0.875rem;
    line-height: 1.5;
}

.dialog-actions {
    display: flex;
    gap: 0.6rem;
    justify-content: center;
    margin-top: 1.25rem;
}
</style>
