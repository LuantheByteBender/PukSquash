# Vue Composables Folder

## Purpose and area

This **frontend** folder holds small pieces of shared reactive state and reusable
logic, written as Vue composition functions.

## Existing composables

| Composable      | Purpose                                                                     |
| --------------- | --------------------------------------------------------------------------- |
| `useAuth.js`    | Exposes the currently signed-in Firebase user as reactive state. Wraps `onAuthStateChanged`. |
| `useAccount.js` | Manages the application user profile — refresh, update, add funds, update coaching fee. Shared across pages. |
| `useToast.js`   | Application-wide toast message queue for success/error notifications.       |

A composable is the lighter alternative to a state-management library. The project
does not use one, and for the amount of shared state here it does not need one. If
shared state grows well beyond this, that decision is worth revisiting as a team.

## Why `useAuth` loads Firebase lazily

`services/firebase.js` calls `getAuth()` at module load, which **throws** when the
`VITE_FIREBASE_*` values are missing from `.env`. Anything that imports it
statically therefore takes the whole application down with a blank white page on a
freshly cloned repository.

`useAuth` imports it inside a `try`/`catch` instead, so an unconfigured environment
degrades to "nobody is signed in" rather than to a broken application.

## Important

Whatever `useAuth` reports is a convenience for the interface. It is never a
security control — Laravel re-checks identity and role on every protected request.

## Beginner mistakes to avoid

- Do not make a composable that talks to the backend directly; call a `service` or
  a `feature` from it.
- Do not create module-level state you did not mean to share. Anything declared
  outside the exported function is shared by every component that uses it — which
  is exactly the point in `useAuth` and `useAccount`, and would be a bug elsewhere.
