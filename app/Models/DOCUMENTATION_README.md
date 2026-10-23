# Laravel Models Folder

## Purpose and area

This **Laravel backend** folder contains Eloquent models. Each model represents a table in the Supabase PostgreSQL database and defines attributes, type conversions, and relationships.

## Existing models

| Model                | Table                  | Purpose                                      |
| -------------------- | ---------------------- | -------------------------------------------- |
| `User`               | `users`                | Application user profile linked to Firebase UID |
| `Court`              | `courts`               | A physical squash court (ACTIVE / INACTIVE)  |
| `Booking`            | `bookings`             | Reservation of a court + time-slot on a date |
| `BookingPlayer`      | `booking_players`      | Join table: player positions per booking     |
| `TimeSlot`           | `time_slots`           | Fixed one-hour booking windows               |
| `CoachAvailability`  | `coach_availabilities` | A coach's published availability             |

These models connect to the Supabase PostgreSQL database via Laravel's `pgsql` driver configured in `.env`.

## Example

`Booking` has relationships to `Court`, `TimeSlot`, and `BookingPlayer`. Controllers query bookings through Eloquent and return data via DTOs.

## Beginner mistakes to avoid

- Update mass-assignment rules carefully; adding sensitive fields to `$fillable` can create security problems.
- A model change does not alter the database schema — create a migration under `database/migrations` as well.
- Keep business logic in services or controllers, not in model methods.

