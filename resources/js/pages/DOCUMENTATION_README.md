# Vue Pages Folder

## Purpose and area

This **frontend** folder contains page-sized Vue components. Pages assemble reusable components and coordinate the data needed for a complete screen.

## Existing files

- `AuthTest.vue` currently demonstrates registration, login, logout, email verification, and a protected Laravel API call.
- `HomePage.vue`, `LoginPage.vue`, and `SignUpPage.vue` exist but are currently empty and are normal files for developers to implement.

Pages connect to `features` for feature behaviour, `services` for external calls, and `components` for reusable presentation. `App.vue` currently imports `AuthTest.vue`; if routing is added later, the router would select which page is displayed.

## Example

When building the real login screen, implement `LoginPage.vue`, call `features/auth/authService.js`, display useful validation errors, and then arrange for `App.vue` or a future router to show it.

## Beginner mistakes to avoid

- Do not put private credentials or backend-only validation in a page.
- Do not assume frontend validation is sufficient; Laravel or Data Connect must still enforce security rules.
- Avoid putting every reusable element directly in a page; move repeated interface pieces to `components`.

