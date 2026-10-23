# Laravel Services Folder

## Purpose and area

This **backend integration and business-logic** folder contains reusable services. Services keep external-system details and multi-step operations out of routes and controllers.

## Existing services

| Service                  | Purpose                                                                |
| ------------------------ | ---------------------------------------------------------------------- |
| `FirebaseAuthService`    | Verifies Firebase ID tokens and fetches Firebase user records. Used by `FirebaseAuth` middleware. |
| `PayfastService`         | Handles PayFast payment integration — signature generation, ITN verification, and payment processing. |
| `SqlConnectClient`       | Client for Firebase SQL Connect GraphQL queries (legacy data access layer). |

## Example

`FirebaseAuthService` is injected into the `FirebaseAuth` middleware to verify tokens on every authenticated request. `PayfastService` is used by `PayfastItnController` and `RegistrationController` for payment flows.

## Beginner mistakes to avoid

- Never hard-code or commit service-account JSON content or PayFast credentials.
- Keep HTTP response formatting in controllers; services should expose useful application operations.
- Do not create a service merely as another name for a single trivial model call.

