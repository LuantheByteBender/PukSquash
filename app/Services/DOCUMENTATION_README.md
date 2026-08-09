# Laravel Services Folder

## Purpose and area

This **backend integration and business-logic** folder contains reusable services. Services keep external-system details and multi-step operations out of routes and controllers.

## Existing file developers edit

`FirebaseAuthService.php` builds the server-side Firebase Authentication client from the credential path in `FIREBASE_CREDENTIALS`. It verifies ID tokens and fetches Firebase user records. `app/Http/Middleware/FirebaseAuth.php` uses this service.

Future services can coordinate substantial booking, court, or user-profile rules when those responsibilities do not fit naturally in a controller or model.

## Example

When several endpoints must check for overlapping reservations, a booking service could centralize that transaction and return a clear result to each controller.

## Beginner mistakes to avoid

- Never hard-code or commit service-account JSON content.
- Keep HTTP response formatting in controllers; services should expose useful application operations.
- Do not create a service merely as another name for a single trivial model call.

