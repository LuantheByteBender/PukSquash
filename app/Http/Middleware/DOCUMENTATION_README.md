# Laravel Middleware Folder

## Purpose and area

This **backend security and request-pipeline** folder contains middleware. Middleware examines or changes a request before it reaches a route/controller, or adjusts a response afterward.

## Existing file developers edit

`FirebaseAuth.php` reads the bearer token, verifies it through `FirebaseAuthService`, fetches the Firebase user, and stores the user's UID, email, and verification status on the Laravel request. Its `firebase.auth` alias is registered in `bootstrap/app.php` and used by `routes/api.php`.

Additional middleware belongs here only when a rule applies across multiple endpoints, such as requiring an administrator role.

## Example

Use the existing `firebase.auth` middleware on a booking route so only a caller with a valid Firebase ID token reaches the controller.

## Beginner mistakes to avoid

- Do not confuse authentication (who the user is) with authorization (what the user may do).
- Do not log bearer tokens, passwords, or private Firebase details.
- Avoid returning internal exception messages in production; they may reveal configuration or security information.

