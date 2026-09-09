# Data Connect Schema Folder

## Purpose and area

This is the **Firebase Data Connect / PostgreSQL database-schema** folder. It is the **sole source of truth** for the application's data model. Laravel does NOT manage these tables via migrations or Eloquent — it talks to them through the SQL Connect GraphQL connector exclusively.

## Phase 1 tables

`schema.gql` currently defines six tables:

| Table                | Purpose                                                     |
| -------------------- | ----------------------------------------------------------- |
| **User**             | Application profile linked to a Firebase Auth UID.          |
| **Court**            | A physical squash court (ACTIVE / INACTIVE).                |
| **TimeSlot**         | Fixed one-hour booking windows.                             |
| **Booking**          | Reservation of a court + time-slot on a date.               |
| **BookingPlayer**    | Join table: Player 1 / Player 2 positions per booking.      |
| **CoachAvailability**| A coach's published availability for a date + slot.         |

## Architecture

```
Vue frontend → Laravel REST API → Firebase SQL Connect GraphQL → PostgreSQL
Firebase Auth → ID token → Laravel middleware
```

Laravel handles **business logic, validation and authorization only**. All data access goes through `App\Services\SqlConnectClient` → `App\Repositories\*`.

## Beginner mistakes to avoid

- Do not create Laravel migrations or Eloquent models for these tables.
- Do not use free-form status strings without considering allowed values.
- Relationships, uniqueness rules, required fields, defaults, and indexes affect data integrity.
- A schema change can break existing operations — update queries, mutations, DTOs and repositories together.
- Do not add Firebase service-account secrets to schema files.

