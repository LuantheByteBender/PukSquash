<script setup>
import { ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';
import { useAccount, refreshAccount, clearAccount } from '../composables/useAccount.js';

const router = useRouter();
const { isSignedIn, displayName } = useAuth();
const { role } = useAccount();

const open = ref(false);
const confirmingSignOut = ref(false);

watch(isSignedIn, async (signedIn) => {
    if (!signedIn) {
        clearAccount();
        open.value = false;
        return;
    }

    try {
        await refreshAccount();
    } catch {
    }
}, { immediate: true });

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
        <div class="inner">
            <RouterLink :to="{ name: isSignedIn ? 'book' : 'login' }" class="brand" @click="open = false">
                <span class="mark" aria-hidden="true">PS</span>
                <span>PukSquash</span>
            </RouterLink>

            <div class="spacer"></div>

            <div v-if="isSignedIn" class="menu">
                <button
                    class="menu-btn"
                    type="button"
                    :aria-expanded="open"
                    aria-label="User menu"
                    @click="open = !open"
                >
                    {{ displayName }}<span class="caret" aria-hidden="true">▾</span>
                </button>

                <div v-if="open" class="menu-panel" role="menu">
                    <RouterLink :to="{ name: 'book' }" class="menu-item" role="menuitem" @click="open = false">
                        Book a court
                    </RouterLink>

                    <RouterLink :to="{ name: 'my-bookings' }" class="menu-item" role="menuitem" @click="open = false">
                        My bookings
                    </RouterLink>

                    <RouterLink :to="{ name: 'coach-notice-board' }" class="menu-item" role="menuitem" @click="open = false">
                        Coach notice board
                    </RouterLink>

                    <RouterLink
                        :to="{ name: 'notice-board' }"
                        class="menu-item"
                        role="menuitem"
                        @click="open = false"
                    >
                        Coach Notice Board
                    </RouterLink>

                    <RouterLink
                        v-if="role === 'COACH'"
                        :to="{ name: 'coach-bookings' }"
                        class="menu-item"
                        role="menuitem"
                        @click="open = false"
                    >
                        Coach availability
                    </RouterLink>

                    <RouterLink
                        v-if="role === 'ADMIN'"
                        :to="{ name: 'admin-dashboard' }"
                        class="menu-item"
                        role="menuitem"
                        @click="open = false"
                    >
                        Admin dashboard
                    </RouterLink>

                    <RouterLink
                        v-if="role === 'ADMIN'"
                        :to="{ name: 'admin-users' }"
                        class="menu-item"
                        role="menuitem"
                        @click="open = false"
                    >
                        Manage users
                    </RouterLink>

                    <RouterLink
                        v-if="role === 'ADMIN'"
                        :to="{ name: 'admin-courts' }"
                        class="menu-item"
                        role="menuitem"
                        @click="open = false"
                    >
                        Manage courts
                    </RouterLink>

                    <RouterLink
                        :to="{ name: 'user-profile' }"
                        class="menu-item"
                        role="menuitem"
                        @click="open = false"
                    >
                        Profile
                    </RouterLink>

                    <button class="menu-item danger" type="button" role="menuitem" @click="requestSignOut">
                        Sign out
                    </button>
                </div>
            </div>

            <RouterLink v-else :to="{ name: 'login' }" class="link" @click="open = false">
                Sign in
            </RouterLink>
        </div>

        <div v-if="confirmingSignOut" class="backdrop" @click.self="confirmingSignOut = false">
            <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="signout-title">
                <h2 id="signout-title">Sign out?</h2>

                <p class="muted">Are you sure you want to sign out?</p>

                <div class="actions">
                    <button class="btn btn-secondary" type="button" @click="confirmingSignOut = false">Cancel</button>
                    <button class="btn btn-danger" type="button" @click="signOut">Sign out</button>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
.nav {
    background: var(--ps-brand);
    color: #fff;
}

.inner {
    max-width: 1120px;
    margin: 0 auto;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.brand {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #fff;
    text-decoration: none;
    font-weight: 700;
    font-size: 1.05rem;
}

.mark {
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.18);
    font-size: 0.75rem;
    letter-spacing: 0.04em;
}

.links {
    display: flex;
    align-items: center;
    gap: 0.15rem;
    flex: 1;
    flex-wrap: wrap;
}

.spacer {
    flex: 1;
}

.menu {
    position: relative;
}

.menu-btn {
    border: 0;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.14);
    color: #fff;
    font: inherit;
    font-weight: 650;
    padding: 0.45rem 0.65rem;
    border-radius: var(--ps-radius-sm);
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.menu-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.caret {
    font-size: 0.85rem;
    line-height: 1;
}

.menu-panel {
    position: absolute;
    right: 0;
    top: calc(100% + 0.5rem);
    min-width: 200px;
    background: #fff;
    color: var(--ps-text);
    border-radius: var(--ps-radius);
    box-shadow: 0 12px 28px rgba(24, 14, 33, 0.25);
    overflow: hidden;
    z-index: 30;
}

.menu-item {
    display: block;
    width: 100%;
    text-align: left;
    padding: 0.65rem 0.85rem;
    background: none;
    border: 0;
    cursor: pointer;
    text-decoration: none;
    color: inherit;
    font: inherit;
    font-weight: 600;
}

.menu-item:hover {
    background: rgba(108, 61, 145, 0.08);
}

.menu-item.danger {
    color: #b42318;
}

.backdrop {
    position: fixed;
    inset: 0;
    z-index: 60;
    padding: 1rem;
    background: rgba(24, 14, 33, 0.5);
    display: grid;
    place-items: center;
}

.dialog {
    max-width: 420px;
    width: 100%;
    background: var(--ps-surface);
    border-radius: var(--ps-radius);
    padding: 1.1rem;
    box-shadow: 0 10px 30px rgba(24, 14, 33, 0.25);
}

.dialog h2 {
    margin: 0 0 0.5rem;
    font-size: 1.15rem;
}

.muted {
    color: var(--ps-text-muted);
    margin: 0;
}

.actions {
    display: flex;
    gap: 0.6rem;
    justify-content: flex-end;
    margin-top: 1rem;
}

.link {
    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    padding: 0.4rem 0.6rem;
    border-radius: var(--ps-radius-sm);
    font-size: 0.9rem;
    font-weight: 550;
    white-space: nowrap;
}

.link:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
}

.link.router-link-active {
    background: rgba(255, 255, 255, 0.18);
    color: #fff;
}

.who {
    font-size: 0.88rem;
    font-weight: 600;
    padding: 0 0.4rem;
}

.burger {
    display: none;
    background: none;
    border: 0;
    cursor: pointer;
    padding: 0.4rem;
}

.burger span {
    display: block;
    width: 20px;
    height: 2px;
    background: #fff;
    margin: 4px 0;
    border-radius: 2px;
}

@media (max-width: 720px) {
    .burger {
        display: block;
        margin-left: auto;
    }

    .links {
        display: none;
        width: 100%;
        flex-direction: column;
        align-items: stretch;
        padding-bottom: 0.5rem;
    }

    .links.open {
        display: flex;
    }

    .spacer {
        display: none;
    }

    .link,
    .who {
        width: 100%;
    }

    .links .btn {
        width: 100%;
    }
}
</style>
