import { ref, computed, readonly } from 'vue';

/**
 * Shared authentication state for the Vue application.
 *
 * A composable rather than a store: the booking feature needs to know who is
 * signed in, and that is all. Adding a state-management dependency for one small
 * piece of shared state would be a bigger change than this branch needs.
 *
 * Whatever this reports is a convenience for the interface. It is never a
 * security control — Laravel re-checks identity and role on every protected
 * request against the verified Firebase token.
 */

const user = ref(null);
const ready = ref(false);
const authAvailable = ref(true);

let started = false;

/**
 * Firebase is imported dynamically and inside a try/catch on purpose.
 *
 * `services/firebase.js` calls getAuth() at module load, which throws
 * `auth/invalid-api-key` when the VITE_FIREBASE_* values are missing from .env.
 * A static import here would mean that a developer who has just cloned the repo,
 * before obtaining the Firebase configuration, gets a blank white page instead of
 * the application. Loading it lazily keeps that failure contained to the parts
 * that genuinely need authentication.
 */
async function start() {
    if (started) return;
    started = true;

    try {
        const [{ auth }, { onAuthStateChanged }] = await Promise.all([
            import('../services/firebase.js'),
            import('firebase/auth'),
        ]);

        onAuthStateChanged(auth, (next) => {
            user.value = next;
            ready.value = true;
        });
    } catch (error) {
        console.warn(
            '[PukSquash] Firebase Auth is unavailable — check the VITE_FIREBASE_* values in .env.',
            error?.message
        );

        authAvailable.value = false;
        ready.value = true;
    }
}

export function useAuth() {
    start();

    return {
        user: readonly(user),
        ready: readonly(ready),
        authAvailable: readonly(authAvailable),
        isSignedIn: computed(() => Boolean(user.value)),
        email: computed(() => user.value?.email || null),
        displayName: computed(
            () => user.value?.displayName || user.value?.email?.split('@')[0] || 'Student'
        ),
        emailVerified: computed(() => Boolean(user.value?.emailVerified)),
    };
}
