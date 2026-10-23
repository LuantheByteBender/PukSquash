# API Controllers Folder

## Purpose and area

This **Laravel backend API** folder contains all JSON controllers that serve the Vue frontend. Every controller here returns JSON responses and is wired up in `routes/api.php`.

## Existing controllers

| Controller                          | Purpose                                         |
| ----------------------------------- | ----------------------------------------------- |
| `UserController`                    | Current user profile (`/me`), funds, coaching fee |
| `BookingController`                 | Create, join, cancel bookings; list own bookings |
| `CourtController`                   | List and show courts                            |
| `TimeSlotController`                | List time slots                                 |
| `AvailabilityController`            | Availability grid data                          |
| `CoachAvailabilityController`       | Coach availability CRUD and notice board        |
| `CoachBookingController`            | Upcoming coach bookings                         |
| `TeamPracticeController`            | Team practice availability and booking          |
| `RegistrationController`            | Registration status and payment initiation      |
| `RegistrationReconcileController`   | Registration payment reconciliation             |
| `PayfastItnController`              | PayFast ITN (Instant Transaction Notification)  |
| `PayfastDemoController`             | Demo payment completion for development         |
| `AdminUserController`               | Admin user management                           |
| `AdminCourtController`              | Admin court management                          |
| `AdminDashboardController`          | Admin stats and utilisation                     |
| `AdminRegistrationPaymentController`| Admin registration payment listing              |

All controllers use Firebase authentication via middleware. Admin controllers additionally require the `admin` middleware.

## Beginner mistakes to avoid

- API controllers should return JSON, not the Vue application's Blade shell.
- Protect sensitive actions with authentication and role-based authorization.
- Use DTOs from `app/DataTransferObjects` to structure response data consistently.

