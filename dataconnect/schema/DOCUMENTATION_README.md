# Data Connect Schema Folder (Legacy)

## ⚠️ Status: Legacy

This folder was the **original Firebase Data Connect / PostgreSQL database-schema** definition. **Supabase PostgreSQL with Laravel Eloquent** is now the active data layer. This schema is retained for historical reference.

## Original tables defined in `schema.gql`

| Table                | Purpose                                                     |
| -------------------- | ----------------------------------------------------------- |
| **User**             | Application profile linked to a Firebase Auth UID.          |
| **Court**            | A physical squash court (ACTIVE / INACTIVE).                |
| **TimeSlot**         | Fixed one-hour booking windows.                             |
| **Booking**          | Reservation of a court + time-slot on a date.               |
| **BookingPlayer**    | Join table: Player 1 / Player 2 positions per booking.      |
| **CoachAvailability**| A coach's published availability for a date + slot.         |

## Current architecture

```
Vue frontend → Laravel REST API → Eloquent Models → Supabase PostgreSQL
Firebase Auth → ID token → Laravel middleware
```

These same entities now exist as Eloquent models in `app/Models/` and are managed through Laravel migrations in `database/migrations/`.

## Beginner mistakes to avoid

- Do not add new schema definitions here — use Laravel migrations instead.
- For the current data model, refer to `app/Models/` and `database/migrations/`.

