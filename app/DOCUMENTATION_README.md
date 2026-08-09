# Laravel Application Folder

## Purpose and area

`app` contains the project's hand-written **Laravel backend** code. This is where requests are processed, authentication is enforced, business rules are implemented, and Laravel models are defined.

## What belongs here

- `Http` contains controllers, middleware, and request validation.
- `Models` contains Laravel Eloquent models.
- `Services` contains reusable backend integrations and business operations.
- `Providers` contains Laravel application bootstrapping and service registration.

Developers normally edit classes inside those folders. Routes in `routes/api.php` or `routes/web.php` direct requests into this code, and application services may communicate with Firebase or a database.

## Example

When adding an authenticated booking API, define its URL in `routes/api.php`, validate its input under `Http/Requests`, process it through an API controller, and place reusable booking rules in an appropriate service.

## Beginner mistakes to avoid

- Do not edit framework code under `vendor`; extend the application through this folder.
- Keep controllers focused on HTTP input and output instead of placing every business rule in them.
- Do not trust data merely because it came from the frontend; authenticate, authorize, and validate it on the backend.

