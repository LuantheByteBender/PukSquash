# Booking Feature Folder

## Purpose and area

This is a **frontend** feature folder. It contains the behaviour behind court
booking: fetching live availability, creating a booking, joining an open booking,
listing a student's own bookings and cancelling them.

## What belongs here

- `bookingService.js` is the only thing pages and components should import. It
  exposes the booking operations and is the single place any endpoint path
  appears.

Presentation lives in `pages/BookCourtPage.vue`, `pages/MyBookingsPage.vue` and the
booking components in `components/`. Those never call `fetch` directly.

## Data source

There is no client-side data source. Every booking operation calls the Laravel API
through `services/http.js`. The earlier `mockBookingData.js` stand-in and the
`VITE_USE_MOCK_BOOKINGS` flag have been removed, so the screens now show exactly
what the backend returns — including nothing, and including its errors. Do not
reintroduce a browser-side data source: it drifts from the backend, and the drift
looks exactly like a booking bug.

Until the endpoints below exist, `/book` and `/my-bookings` will show the API's own
error. That is the intended state, not a regression.

> **Note:** `dataconnect/schema/schema.gql` cannot currently store this model —
> it has no time slots, no booking type, no booking players and no coach
> availability. The schema needs updating before the backend can implement these
> endpoints. See the repository review notes.

## Endpoints the backend must provide

All paths are relative to `/api`. Every one is authenticated: `services/http.js`
sends the Firebase ID token as `Authorization: Bearer <token>` and nothing else.
Laravel must derive the acting user from that verified token — the client never
sends a user id, and anything it did send could be forged.

| Method | Path | Serves |
|---|---|---|
| `GET` | `/api/availability?date=YYYY-MM-DD` | FR-06 |
| `GET` | `/api/coaches/available?date=&time_slot_id=` | FR-09 |
| `POST` | `/api/bookings` | FR-07 / FR-08 / FR-09 |
| `POST` | `/api/bookings/{id}/join` | FR-10 / FR-11 |
| `GET` | `/api/bookings/mine?scope=upcoming\|past\|cancelled` | FR-15 |
| `POST` | `/api/bookings/{id}/cancel` | FR-16 |

### `GET /api/availability`

```jsonc
{
  "booking_window_days": 14,          // how far ahead the date picker may go
  "courts":     [{ "id": "uuid", "name": "Court 1", "status": "ACTIVE" }],
  "time_slots": [{ "id": "uuid", "start_time": "07:00:00", "end_time": "08:00:00",
                   "display_order": 1 }],
  "cells": [
    {
      "court_id":     "uuid",
      "time_slot_id": "uuid",
      "state":        "AVAILABLE",    // AVAILABLE | OPEN | FULL | LOCKED |
                                      // PRIVATE_COACH | UNAVAILABLE
      "label":        "Available",    // the exact text the cell shows
      "can_book":     true,           // may this user start a booking here
      "can_join":     false,          // may this user take player position 2
      "is_mine":      false,          // marks the cell with the "You" tag
      "booking_id":   null            // required whenever can_join is true
    }
  ]
}
```

Every cell's state must arrive **already decided**, not as raw booking rows. The
grid renders `state`, `label`, `can_book`, `can_join`; it derives nothing. A cell
missing from `cells` renders as `UNAVAILABLE`.

### `POST /api/bookings`

```jsonc
{
  "court_id":     "uuid",
  "time_slot_id": "uuid",
  "booking_date": "2026-08-27",
  "booking_type": "OPEN",   // OPEN | LOCKED | PRIVATE_COACH
  "coach_id":     null      // required when booking_type is PRIVATE_COACH
}
```

Create, join and cancel all return the affected booking, shaped like the entries in
`/api/bookings/mine`:

```jsonc
{
  "id": "uuid",
  "booking_date": "2026-08-27",
  "booking_type": "OPEN",
  "status": "CONFIRMED",
  "court":     { "id": "uuid", "name": "Court 1" },
  "time_slot": { "id": "uuid", "start_time": "07:00:00", "end_time": "08:00:00" },
  "coach":     null,                                    // or { "id", "name" }
  "players":   [{ "user_id": "uuid", "name": "…", "player_position": 1 }]
}
```

`POST /api/bookings/{id}/cancel` may also return `"left": true` when the caller was
player 2 leaving an open session rather than cancelling their own booking — the
other player keeps the court.

### Errors

A cell the user cannot have must come back as **409** with a `code`
(`SLOT_TAKEN`, `BOOKING_FULL`, `BOOKING_GONE`, `COACH_UNAVAILABLE`). The interface
treats 409 as a normal outcome: it closes the dialog, says so plainly and refreshes
the grid.

Anything the user can act on — a booking limit reached, a date outside the window,
an inactive court — should be **422** (or 400) with a human-readable `message`.
That message is shown to the student verbatim, inside the dialog they are already
looking at, so write it for them rather than for a log.

## Beginner mistakes to avoid

- Do not call `fetch` from a page or component; go through `bookingService`.
- Do not update a grid cell locally after booking. Re-fetch. Seeing a cell as
  available never reserved it, and a locally patched cell hides that.
- Do not treat a 409 as a crash. It means another student got there first.
- Do not send a user id to identify the caller. Laravel reads the identity from
  the verified Firebase token; anything we send could be forged.
- Do not add booking rules here expecting them to be enforced. Rules in the
  browser are a convenience; Laravel and PostgreSQL decide.
