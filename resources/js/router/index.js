import { watch } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import { useAuth } from '../composables/useAuth.js';
import { useAccount, refreshAccount } from '../composables/useAccount.js';
import { http, ApiError } from '../services/http.js';

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
        component: () => import('../pages/SplashScreen.vue'),
        meta: { chrome: false },
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
        path: '/registration/payment',
        name: 'registration-payment',
        component: () => import('../pages/RegistrationPaymentPage.vue'),
        meta: { chrome: false },
    },
    {
        path: '/payment/return',
        name: 'payment-return',
        component: () => import('../pages/PaymentReturnPage.vue'),
        meta: { chrome: false },
    },
    {
        path: '/payment/cancel',
        name: 'payment-cancel',
        component: () => import('../pages/PaymentCancelPage.vue'),
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
        path: '/admin/registration-payments',
        name: 'admin-registration-payments',
        component: () => import('../pages/RegistrationPaymentsPage.vue'),
        meta: { requiresRole: 'ADMIN' },
    },
    {
        path: '/profile',
        name: 'user-profile',
        component: () => import('../pages/UserProfile.vue'),
    },

    {
        path: '/notice-board',
        name: 'notice-board',
        component: () => import('../pages/CoachNoticeBoardPage.vue'),
    },

    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        redirect: { name: 'home' },
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
        let stop = () => {};
        const timeout = setTimeout(() => {
            stop();
            console.warn('[PukSquash] Timed out waiting for auth readiness.');
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

router.beforeEach(async (to) => {
    const { ready, isSignedIn } = useAuth();
    const { role } = useAccount();

    await waitForTruthy(ready);

    if (to.name === 'home' || to.name === 'login' || to.name === 'signup') {
        return true;
    }

    if (!isSignedIn.value) {
        return {
            name: 'login',
            query: to.fullPath ? { redirect: to.fullPath } : undefined,
        };
    }

    if (
        to.name === 'registration-payment' ||
        to.name === 'payment-return' ||
        to.name === 'payment-cancel'
    ) {
        return true;
    }

    if (!role.value) {
        try {
            const status = await http.get('/registration/status');
            if (!status?.registered) {
                try {
                    await http.post('/registration/reconcile');
                    await refreshAccount();
                } catch {
                }

                if (!role.value) {
                    return {
                        name: 'registration-payment',
                        query: to.fullPath ? { redirect: to.fullPath } : undefined,
                    };
                }
            }
        } catch (e) {
            if (e instanceof ApiError && e.code === 'EMAIL_NOT_VERIFIED') {
                return { name: 'login' };
            }

            if (e?.status === 401) {
                return { name: 'login' };
            }
        }
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
                name: 'registration-payment',
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
