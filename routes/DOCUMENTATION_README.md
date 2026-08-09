# Laravel Routes Folder

## Purpose and area

This **backend routing** folder defines how incoming URLs and console commands enter the Laravel application.

## Existing files developers edit

- `web.php` returns the `app` Blade view for frontend paths so Vue can run.
- `api.php` defines API endpoints. It currently provides protected `GET /api/me` using the `firebase.auth` middleware.
- `console.php` is for custom Artisan console commands and schedules expressed as closures.

Routes connect URLs to middleware, closures, and controller actions. The route files are registered in `bootstrap/app.php`.

## Example

When adding `GET /api/courts`, define the route in `api.php`, attach the appropriate authentication middleware, and point it to an API controller action.

## Beginner mistakes to avoid

- API routes already receive the `/api` prefix; do not accidentally duplicate it in the route path.
- Keep complex business logic out of route closures—move growing endpoints into controllers and services.
- Order and wildcard routes matter. The catch-all web route must not accidentally replace intended backend routes.

