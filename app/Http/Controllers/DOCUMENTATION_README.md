# Laravel Controllers Folder

## Purpose and area

This **backend** folder contains Laravel controllers. A controller receives a routed HTTP request, coordinates validation and application services, and returns an HTTP response.

## Existing and expected files

`Controller.php` is the base controller that application controllers can extend. The existing `Api` subfolder is intended for JSON API controllers. New controller files should have focused names based on real features, such as a booking controller when booking endpoints are implemented.

Controllers connect `routes` to models, services, and validated requests. Developers normally edit feature-specific controllers rather than the base `Controller.php`.

## Example

A booking controller action might accept a validated request, ask a booking service to create the reservation, and return the new booking as JSON with status `201`.

## Beginner mistakes to avoid

- Do not put route definitions inside controllers; keep them in `routes`.
- Avoid very large controllers containing SQL, authentication, validation, and formatting all in one method.
- Never return raw exception details or secrets to users in production responses.

