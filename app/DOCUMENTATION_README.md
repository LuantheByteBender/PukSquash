# Laravel Application Folder

## Purpose and area

`app` contains the project's hand-written **Laravel backend** code. This is where requests are processed, authentication is enforced, business rules are implemented, and data models are defined.

## What belongs here

- `Http` contains controllers, middleware, and request validation.
- `Models` contains Eloquent models (`User`, `Court`, `Booking`, `BookingPlayer`, `TimeSlot`, `CoachAvailability`).
- `Services` contains reusable backend integrations (`FirebaseAuthService`, `PayfastService`, `SqlConnectClient`).
- `DataTransferObjects` contains DTOs that structure API response data (`UserDTO`, `BookingDTO`, `CourtDTO`, etc.).
- `Providers` contains Laravel application bootstrapping and service registration.

Routes in `routes/api.php` or `routes/web.php` direct requests into this code. Controllers use services for business logic and Eloquent models for Supabase PostgreSQL access.

## Example

When adding a new booking feature, define its URL in `routes/api.php`, validate input under `Http/Requests`, handle the request in an API controller, and return data through a DTO.

## Beginner mistakes to avoid

- Do not edit framework code under `vendor`; extend the application through this folder.
- Keep controllers focused on HTTP input and output instead of placing every business rule in them.
- Do not trust data merely because it came from the frontend; authenticate, authorize, and validate it on the backend.

