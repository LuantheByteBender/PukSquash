# Vue JavaScript Folder

## Purpose and area

`resources/js` is the main **Vue frontend** source folder. It starts the Vue application, organizes pages and reusable components, and communicates with Firebase and Laravel.

## What belongs here

- `app.js` is the browser entry point and mounts `App.vue` to `#app`.
- `App.vue` is the root component and currently renders `pages/AuthTest.vue`.
- `bootstrap.js` contains shared browser and HTTP setup supplied by Laravel.
- `components` contains reusable interface pieces.
- `pages` contains page-sized Vue components.
- `features` groups code belonging to a particular product feature.
- `services` contains integrations such as Firebase setup and Laravel API requests.

Developers will normally edit `App.vue`, pages, components, feature modules, and services. This folder is compiled by Vite according to `vite.config.js` and loaded by `resources/views/app.blade.php`.

## Example

To add a court-booking page, build its interface in `pages`, extract reusable court cards into `components`, and call backend or Data Connect functions through a service.

## Beginner mistakes to avoid

- Do not place Firebase service-account credentials here; all code in this folder can reach the browser.
- Do not mix database queries directly into presentation components when a service or feature module can keep the responsibility clearer.
- The project does not currently have Vue Router or a state-store folder. Do not import router/store packages until they have deliberately been added and configured.

