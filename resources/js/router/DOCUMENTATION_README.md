# Vue Router Folder

## Purpose and area

This **frontend** folder defines which page component is shown for which URL.

## Why routing was added

Before this, `App.vue` chose a page with a `currentPage` ref and the pages talked
to it through events. That works for three screens. It does not survive Phase 1,
which needs a student to be able to open the booking grid directly, to use the
browser Back button after opening a booking, to refresh without losing their
place, and to be sent a link to a specific screen.

`routes/web.php` already returns the `app` view for every path, so browser history
mode works on refresh with **no backend change**.

## What belongs here

`index.js` holds the route table. A route names a path, a page component in
`pages/`, and optional `meta`.

`meta.chrome: false` marks the full-bleed screens (landing, login, sign-up) that
supply their own layout and should not get the navigation bar.

## Important

These routes decide what the browser **shows**. They do not decide what the system
**allows**. Laravel re-checks identity and role on every protected request against
the verified Firebase token. A route guard that is bypassed exposes a screen, not
data — never treat one as an access control.

## Beginner mistakes to avoid

- Do not put data fetching in the route table; pages own their own loading.
- Do not add a guard and assume the matching backend check is unnecessary.
- Do not import page components eagerly at the top of `index.js`. The lazy
  `() => import(...)` form keeps each screen in its own chunk, and it also stops
  one failing module from taking down unrelated screens.
