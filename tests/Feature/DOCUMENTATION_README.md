# Feature Tests Folder

## Purpose and area

This **testing** folder contains Laravel feature tests. They verify behaviour across boundaries such as HTTP routes, middleware, validation, services, and databases.

## Existing file developers edit

`ExampleTest.php` is the starter feature test. Replace or supplement starter assertions with tests representing PukSquash behaviour. Feature tests usually extend `tests/TestCase.php` and may use Laravel's request and database testing helpers.

These tests connect to the backend entry points users actually exercise, such as `/api/me` and future court or booking endpoints.

## Example

Add a test that requests `/api/me` without a bearer token and verifies that the API returns HTTP `401` with a safe JSON message.

## Beginner mistakes to avoid

- Mock Firebase verification or use a dedicated emulator/test project; never depend on private production credentials.
- Assert outcomes and externally visible behaviour rather than framework implementation details.
- Reset or isolate database state so one test cannot influence another.

