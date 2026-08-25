# Booking Feature Folder

## Purpose and area

This is a **frontend** feature folder. It contains the behaviour behind court
booking: fetching live availability, creating a booking, joining an open booking,
listing a student's own bookings and cancelling them.

## What belongs here

- `bookingService.js` is the only thing pages and components should import. It
  exposes the booking operations and decides where the data comes from.
- `mockBookingData.js` is a temporary in-browser stand-in for the Laravel booking
  endpoints, which do not exist yet.

Presentation lives in `pages/BookCourtPage.vue`, `pages/MyBookingsPage.vue` and the
booking components in `components/`. Those never call `fetch` directly.

## The mock data source

`VITE_USE_MOCK_BOOKINGS` controls where booking data comes from. It defaults to the
mock, because Laravel has no booking endpoints yet. Set it to `false` in `.env`
once they exist — no component changes are needed.

`mockBookingData.js` implements the model the approved v2.0 requirements describe:
fixed one-hour time slots, `OPEN` / `LOCKED` / `PRIVATE_COACH` booking types,
booking players, and coach availability. It also enforces the same rules the
backend must, so the interface has to handle the same failures it will meet in
production — the 409 conflicts especially.

It is **not** a security boundary. It runs in the browser, where anyone can edit
it. Delete the file once the real endpoints are live.

> **Note:** `dataconnect/schema/schema.gql` cannot currently store this model —
> it has no time slots, no booking type, no booking players and no coach
> availability. The schema needs updating before the backend can implement these
> endpoints. See the repository review notes.

## Endpoints the backend must provide

| Method | Path |
|---|---|
| `GET` | `/api/availability?date=` |
| `GET` | `/api/coaches/available?date=&time_slot_id=` |
| `POST` | `/api/bookings` |
| `POST` | `/api/bookings/{id}/join` |
| `GET` | `/api/bookings/mine?scope=upcoming\|past\|cancelled` |
| `POST` | `/api/bookings/{id}/cancel` |

`GET /api/availability` must return every cell's state **already decided** —
`state`, `label`, `can_book`, `can_join` — not raw booking rows. If the grid starts
deriving state itself, the two implementations will drift and the drift will look
exactly like a booking bug.

A cell the user cannot have must come back as **409** with a `code`
(`SLOT_TAKEN`, `BOOKING_FULL`, `BOOKING_GONE`, `COACH_UNAVAILABLE`). The interface
treats 409 as a normal outcome: it closes the dialog, says so plainly and refreshes
the grid.

## Beginner mistakes to avoid

- Do not call `fetch` from a page or component; go through `bookingService`.
- Do not update a grid cell locally after booking. Re-fetch. Seeing a cell as
  available never reserved it, and a locally patched cell hides that.
- Do not treat a 409 as a crash. It means another student got there first.
- Do not send a user id to identify the caller. Laravel reads the identity from
  the verified Firebase token; anything we send could be forged.
- Do not add booking rules here expecting them to be enforced. Rules in the
  browser are a convenience; Laravel and PostgreSQL decide.
