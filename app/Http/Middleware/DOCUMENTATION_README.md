# Laravel Middleware Folder

## Purpose and area

This **backend security and request-pipeline** folder contains middleware. Middleware examines or changes a request before it reaches a route/controller, or adjusts a response afterward.

## Existing middleware

| File                       | Alias             | Purpose                                                                  |
| -------------------------- | ----------------- | ------------------------------------------------------------------------ |
| `FirebaseAuth.php`         | `firebase.auth`   | Verifies the Firebase ID token, resolves the user, and attaches it to the request |
| `AdminMiddleware.php`      | `admin`           | Ensures the authenticated user has an admin role                         |
| `EnsureRegisteredUser.php` | `registered`      | Ensures the user has completed registration before accessing protected routes |

These are registered in `bootstrap/app.php` and applied in `routes/api.php`. The middleware chain is: `firebase.auth` → `registered` → `admin` (for admin routes).

## Example

All API routes are wrapped in `firebase.auth`. Booking, court, and coaching routes additionally require `registered`. Admin management routes add the `admin` middleware.

## Beginner mistakes to avoid

- Do not confuse authentication (who the user is) with authorization (what the user may do).
- Do not log bearer tokens, passwords, or private Firebase details.
- Avoid returning internal exception messages in production; they may reveal configuration or security information.

