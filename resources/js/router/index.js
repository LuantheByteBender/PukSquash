import { watch } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';
import { useAccount, refreshAccount } from '../composables/useAccount.js';

/**
 * Application routes.
 *
 * `routes/web.php` already returns the `app` view for every path, so the browser
 * history mode works on a page refresh with no backend change.
 *
 * These routes decide what the browser SHOWS. They do not decide what the system
 * ALLOWS. Laravel re-checks identity and role on every protected request, so a
 * guard that is bypassed exposes a screen, not data.
 */

const routes = [
    {
        path: '/',
        name: 'home',
        redirect: { name: 'login' },
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/LoginPage.vue'),
        meta: { chrome: false },
    },
    {
        path: '/signup',
        name: 'signup',
        component: () => import('../pages/SignUpPage.vue'),
        meta: { chrome: false },
    },

    {
        path: '/auth-test',
        name: 'auth-test',
        component: () => import('../pages/AuthTest.vue'),
        meta: { chrome: false },
    },

    // Booking feature
    {
        path: '/book',
        name: 'book',
        component: () => import('../pages/BookCourtPage.vue'),
    },
    {
        path: '/my-bookings',
        name: 'my-bookings',
        component: () => import('../pages/MyBookingsPage.vue'),
    },

    {
        path: '/coach/bookings',
        name: 'coach-bookings',
        component: () => import('../pages/CoachBookings.vue'),
        meta: { requiresRole: 'COACH' },
    },
    {
        path: '/coach/team-practice',
        name: 'coach-team-practice',
        component: () => import('../pages/CoachTeamPracticeBookingPage.vue'),
        meta: { requiresRole: 'COACH' },
    },
    {
        path: '/coach/notice-board',
        name: 'coach-notice-board',
        component: () => import('../pages/CoachNoticeBoardPage.vue'),
    },
    {
        path: '/coach/profile',
        name: 'coach-profile',
        redirect: { name: 'user-profile' },
    },
    {
        path: '/admin/dashboard',
        name: 'admin-dashboard',
        component: () => import('../pages/AdminDashboard.vue'),
        meta: { requiresRole: 'ADMIN' },
    },
    {
        path: '/admin/users',
        name: 'admin-users',
        component: () => import('../pages/ManageUsersPage.vue'),
        meta: { requiresRole: 'ADMIN' },
    },
    {
        path: '/admin/courts',
        name: 'admin-courts',
        component: () => import('../pages/ManageCourtsPage.vue'),
        meta: { requiresRole: 'ADMIN' },
    },
    {
        path: '/profile',
        name: 'user-profile',
        component: () => import('../pages/UserProfile.vue'),
    },

    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        redirect: { name: 'home' },
    },

    {
        path: '/notice-board',
        name: 'notice-board',
        component: () => import('../pages/CoachNoticeBoardPage.vue'),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});

function waitForTruthy(source) {
    if (source.value) {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        const stop = watch(source, (value) => {
            if (!value) return;
            stop();
            resolve();
        });
    });
}

router.beforeEach(async (to) => {
    const { ready, isSignedIn } = useAuth();
    const { role } = useAccount();

    await waitForTruthy(ready);

    if (to.name === 'login' || to.name === 'signup') {
        return true;
    }

    if (!isSignedIn.value) {
        return {
            name: 'login',
            query: to.fullPath ? { redirect: to.fullPath } : undefined,
        };
    }

    const requiredRole = to.meta?.requiresRole;
    if (!requiredRole) {
        return true;
    }

    if (!role.value) {
        try {
            await refreshAccount();
        } catch {
            return {
                name: 'login',
                query: to.fullPath ? { redirect: to.fullPath } : undefined,
            };
        }
    }

    if (role.value !== requiredRole) {
        return { name: 'book' };
    }

    return true;
});

export default router;
