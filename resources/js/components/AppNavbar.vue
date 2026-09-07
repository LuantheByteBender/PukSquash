<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';

const router = useRouter();
const { isSignedIn, displayName } = useAuth();

const open = ref(false);

async function signOut() {
    open.value = false;

    try {
        // Loaded on demand: authService imports services/firebase.js, which
        // throws at module load when Firebase is not configured yet.
        const { logoutUser } = await import('../features/auth/authService.js');
        await logoutUser();
    } catch {
        // Already signed out, or Firebase is unavailable — either way, leave.
    }

    router.push({ name: 'home' });
}
</script>

<template>
    <header class="nav">
        <div class="inner">
            <RouterLink :to="{ name: 'home' }" class="brand" @click="open = false">
                <span class="mark" aria-hidden="true">PS</span>
                <span>PukSquash</span>
            </RouterLink>

            <button class="burger" :aria-expanded="open" aria-label="Menu" @click="open = !open">
                <span></span><span></span><span></span>
            </button>

            <nav class="links" :class="{ open }">
                <RouterLink :to="{ name: 'book' }" class="link" @click="open = false">
                    Book a court
                </RouterLink>

                <RouterLink :to="{ name: 'my-bookings' }" class="link" @click="open = false">
                    My bookings
                </RouterLink>

                <span class="spacer"></span>

                <template v-if="isSignedIn">
                    <span class="who">{{ displayName }}</span>
                    <button class="btn btn-secondary btn-sm" @click="signOut">Sign out</button>
                </template>

                <RouterLink v-else :to="{ name: 'signup' }" class="link" @click="open = false">
                    Sign in
                </RouterLink>
            </nav>
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
