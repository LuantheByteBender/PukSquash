# Authentication Feature

## Purpose and area

This **Vue frontend and Firebase** folder contains user-facing authentication operations. It wraps Firebase Authentication functions in application-specific functions that pages can call.

## Existing file developers edit

`authService.js` exports functions for registering a user, sending an email-verification message, logging in, logging out, and sending password-reset emails. It imports the initialized authentication object from `resources/js/services/firebase.js` and is used by `LoginPage.vue`, `SignUpPage.vue`, and other auth-related pages.

Backend token verification does not belong here; it is implemented by Laravel under `app/Http/Middleware` and `app/Services`.

## Example

When implementing `LoginPage.vue`, import `loginUser` from `authService.js` instead of calling the Firebase SDK directly in several pages.

## Beginner mistakes to avoid

- Firebase browser configuration is not a service-account credential; never add private JSON keys here.
- Do not rely only on `emailVerified` checks in Vue. Protected backend/database operations must enforce their own verification and authorization.
- Return useful results and errors to the caller rather than changing unrelated page state inside this service.

