# Frontend Features Folder

## Purpose and area

This is a **frontend** organization folder. It groups code by product feature so that related behaviour does not become scattered across generic utility files.

## Existing features

| Feature    | Path              | Purpose                                                        |
| ---------- | ----------------- | -------------------------------------------------------------- |
| `auth`     | `features/auth/`  | Firebase authentication operations (login, register, logout, password reset) |
| `booking`  | `features/booking/` | Court booking operations (availability, create, join, cancel, list bookings) |

Feature code calls `services` (especially `http.js`) and is used by `pages` and `components`. Each feature folder has its own `DOCUMENTATION_README.md` with detailed information.

## Example

When adding a new booking operation, work in `features/booking/bookingService.js`. When adding a new auth flow, work in `features/auth/authService.js`.

## Beginner mistakes to avoid

- Do not create empty feature folders merely to predict future needs.
- Avoid duplicating generic API or Firebase initialization code inside every feature — use `services/`.
- Keep backend authorization in Laravel as well; organizing frontend features does not create a security boundary.

