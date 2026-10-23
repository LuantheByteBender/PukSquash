# Laravel Routes Folder

## Purpose and area

This **backend routing** folder defines how incoming URLs and console commands enter the Laravel application.

## Existing files

- `web.php` returns the `app` Blade view for all frontend paths so Vue Router can handle client-side navigation.
- `api.php` defines the full REST API with the following endpoint groups:
  - **Auth:** `GET /me` — current user profile
  - **Registration:** status, payment initiation, reconciliation
  - **Bookings:** create, join, cancel, list own bookings
  - **Courts & Time Slots:** list and show courts, list time slots
  - **Availability:** availability grid data
  - **Coaching:** coach availability CRUD, notice board, upcoming coach bookings
  - **Team Practice:** availability and booking
  - **Admin:** user management, court management, dashboard stats, registration payments
  - **Payments:** PayFast ITN webhook
- `console.php` is for custom Artisan console commands and schedules.

Routes are registered in `bootstrap/app.php`. All API routes use `firebase.auth` middleware, most require `registered`, and admin routes additionally require `admin`.

## Example

When adding a new endpoint, define the route in `api.php`, attach the appropriate middleware group, and point it to a controller in `app/Http/Controllers/Api`.

## Beginner mistakes to avoid

- API routes already receive the `/api` prefix; do not duplicate it in the route path.
- Keep complex business logic out of route closures — move growing endpoints into controllers and services.
- Order and wildcard routes matter. The catch-all web route must not accidentally replace intended backend routes.

