import { createRouter, createWebHistory } from 'vue-router';

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
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        redirect: { name: 'home' },
    },
];

export default createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior: () => ({ top: 0 }),
});
