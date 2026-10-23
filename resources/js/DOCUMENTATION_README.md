# Vue JavaScript Folder

## Purpose and area

`resources/js` is the main **Vue 3 frontend** source folder. It starts the Vue application, handles client-side routing, organizes pages and reusable components, and communicates with Firebase and the Laravel API.

## What belongs here

- `app.js` is the browser entry point — it creates the Vue app, installs Vue Router, and mounts `App.vue` to `#app`.
- `App.vue` is the root component with the navigation bar and `<router-view>` for page rendering.
- `bootstrap.js` contains shared browser and HTTP setup supplied by Laravel.
- `router/` defines all client-side routes and page mappings.
- `pages/` contains 17+ page-sized Vue components (booking, admin, coaching, auth, etc.).
- `components/` contains reusable UI components (navbar, booking cards, dialogs, grids, etc.).
- `composables/` contains shared reactive state (`useAuth`, `useAccount`, `useToast`).
- `features/` groups feature-specific behaviour (auth, booking).
- `services/` contains integrations (Firebase, HTTP client, API helpers).

This folder is compiled by Vite according to `vite.config.js` and loaded by `resources/views/app.blade.php`.

## Example

To add a new page, create a Vue component in `pages/`, add a route in `router/index.js`, use `services/http.js` to call the Laravel API, and extract reusable UI pieces into `components/`.

## Beginner mistakes to avoid

- Do not place Firebase service-account credentials or Supabase `service_role` keys here; all code in this folder reaches the browser.
- Do not call `fetch` directly from pages — go through `services/http.js` or a feature service.
- Do not mix database queries directly into presentation components when a service or feature module can keep the responsibility clearer.

