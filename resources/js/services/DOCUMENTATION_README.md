# Frontend Services Folder

## Purpose and area

This **frontend integration** folder contains code that communicates with systems outside a Vue component. It keeps connection and request details separate from page markup.

## Existing files

| File           | Purpose                                                                              |
| -------------- | ------------------------------------------------------------------------------------ |
| `firebase.js`  | Initializes the Firebase browser SDK and exports the Authentication instance         |
| `http.js`      | Shared HTTP transport for the Laravel API — attaches Firebase bearer tokens, handles errors including 409 conflicts, and exports `http.get/post/patch/put/delete` helpers |
| `api.js`       | High-level API functions (e.g. `getCurrentUser`) built on `http.js`                  |
| `loading.js`   | Shared loading-state utility                                                         |

Pages and feature modules import these services. `firebase.js` reads `VITE_FIREBASE_*` values from `.env`. `http.js` is the central place where all API requests flow — it handles token refresh on 401, conflict detection on 409, and consistent error formatting.

## Example

When adding a new API call, create a function in the relevant feature service (e.g. `features/booking/bookingService.js`) that uses `http.post('/bookings', data)` from `http.js`.

## Beginner mistakes to avoid

- Never place the Firebase service-account JSON, Supabase `service_role` key, or private keys in this folder.
- Do not call `fetch` directly from pages — use `http.js` so authentication and error handling are consistent.
- Keep UI state and HTML out of service files; return data or throw errors so the calling component can decide what to display.

