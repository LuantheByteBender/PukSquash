# Laravel Controllers Folder

## Purpose and area

This **backend** folder contains Laravel controllers. A controller receives a routed HTTP request, coordinates validation and application services, and returns an HTTP response.

## Existing files

- `Controller.php` is the base controller.
- `Api/` contains all JSON API controllers — see `Api/DOCUMENTATION_README.md` for the full list.

Controllers connect `routes` to models, services, DTOs, and validated requests. Developers normally edit feature-specific controllers inside `Api/`.

## Example

`BookingController` accepts a validated booking request, checks availability, creates the booking via Eloquent, and returns the booking DTO as JSON.

## Beginner mistakes to avoid

- Do not put route definitions inside controllers; keep them in `routes`.
- Avoid very large controllers containing SQL, authentication, validation, and formatting all in one method.
- Never return raw exception details or secrets to users in production responses.

