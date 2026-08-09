# Frontend Services Folder

## Purpose and area

This **frontend integration** folder contains code that communicates with systems outside a Vue component. It keeps connection and request details separate from page markup.

## Existing files developers edit

- `firebase.js` initializes the Firebase browser SDK and exports the Firebase Authentication instance.
- `api.js` obtains the signed-in user's Firebase ID token and sends it to Laravel's protected `/api/me` endpoint.

Pages and feature modules import these services. `firebase.js` reads `VITE_FIREBASE_*` values from `.env`, while `api.js` connects the Vue frontend to routes declared in `routes/api.php`.

## Example

When adding a protected booking endpoint, add a clearly named function here that attaches the Firebase bearer token, handles unsuccessful responses, and returns parsed booking data to the page.

## Beginner mistakes to avoid

- Never place the Firebase service-account JSON or private keys in this folder.
- Check `response.ok` before trusting an API response and present safe error messages to users.
- Keep UI state and HTML out of service files; return data or throw errors so the calling component can decide what to display.

