# PukSquash Developer Guide

PukSquash is a squash court booking system combining a Vue 3 frontend, a Laravel 12 backend, Firebase Authentication, and a Supabase-hosted PostgreSQL database. Start here when you are unsure where a change belongs.

## Where each part lives

- **Frontend:** `resources/js` contains the Vue application — pages, components, composables, features, services, and the Vue Router. `resources/css` has the global Tailwind stylesheet, and `resources/views/app.blade.php` is the HTML shell in which Vue runs.
- **Backend:** `app` contains Laravel controllers, middleware, services, DTOs, models, and request validation. `routes` defines the URLs handled by Laravel.
- **Database:** Supabase (PostgreSQL) is the primary database, connected via Laravel's `pgsql` driver. `database` contains Laravel migrations, seeders, and factories. The legacy `dataconnect` folder contains an earlier Firebase Data Connect schema that is no longer the active data layer.
- **Firebase:** Browser configuration is in `resources/js/services/firebase.js`; backend token verification is in `app/Services/FirebaseAuthService.php` and `app/Http/Middleware/FirebaseAuth.php`.
- **Configuration:** `.env` supplies local settings. `.env.example`, `config`, `vite.config.js`, `package.json`, and `composer.json` describe application and tool configuration.
- **Testing:** `tests/Feature` is for behaviour spanning several parts of Laravel, while `tests/Unit` is for small isolated pieces.

## Good folders for a beginner to inspect first

1. Read `resources/js/App.vue` to see the root layout and how Vue Router renders pages.
2. Read `resources/js/router/index.js` to see all frontend routes and their page components.
3. Read `routes/api.php` to see the full REST API with authentication, registration, booking, coaching, and admin endpoints.
4. Read `app/Http/Middleware/FirebaseAuth.php` to understand how Laravel verifies Firebase tokens.
5. Read `app/Models/` to see the Eloquent models: `User`, `Court`, `Booking`, `BookingPlayer`, `TimeSlot`, and `CoachAvailability`.

## Simplified project tree

```text
PukSquash/
|-- app/                         Laravel backend code
|   |-- DataTransferObjects/     DTOs for API responses
|   |-- Http/                    Controllers, middleware, and validation
|   |-- Models/                  Eloquent models (User, Court, Booking, etc.)
|   |-- Providers/               Service providers
|   `-- Services/                Firebase auth, PayFast, SQL Connect client
|-- config/                      Laravel configuration
|-- database/                    Migrations, factories, and seeders
|-- dataconnect/                 Legacy Firebase Data Connect schema
|-- public/                      Public web entry point and static files
|-- resources/
|   |-- css/                     Global Tailwind styles
|   |-- js/                      Vue application
|   |   |-- components/          Reusable UI components
|   |   |-- composables/         Shared reactive state (useAuth, useAccount, useToast)
|   |   |-- features/            Feature modules (auth, booking)
|   |   |-- pages/               Page components (17+ screens)
|   |   |-- router/              Vue Router configuration
|   |   `-- services/            Firebase, HTTP client, API helpers
|   `-- views/                   Laravel Blade HTML shells
|-- routes/                      Web, API, and console route definitions
|-- tests/                       Automated PHP tests
|-- .env.example                 Safe environment-variable template
|-- package.json                 JavaScript dependencies and scripts
|-- composer.json                PHP dependencies and scripts
`-- vite.config.js               Vue/Vite build configuration
```

## Credentials and environment variables

- **Supabase:** Laravel connects to Supabase PostgreSQL via `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD`. Optional frontend variables `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` are for the Supabase JS client.
- **Firebase:** The Vue app reads `VITE_FIREBASE_API_KEY`, `VITE_FIREBASE_AUTH_DOMAIN`, `VITE_FIREBASE_PROJECT_ID`, `VITE_FIREBASE_STORAGE_BUCKET`, `VITE_FIREBASE_MESSAGING_SENDER_ID`, and `VITE_FIREBASE_APP_ID`. Laravel reads `FIREBASE_CREDENTIALS`, which points to a private service-account JSON file such as `auth.json`.

Never commit `.env`, `auth.json`, private keys, database passwords, or other service-account JSON files. Variables beginning with `VITE_` are included in the browser build and therefore cannot safely contain server secrets.

## Architecture

```text
Vue frontend → Vue Router → Pages → Services/Features → Laravel REST API → Supabase (PostgreSQL)
Firebase Auth → ID token → Laravel FirebaseAuth middleware → verified user
```

Laravel owns all business logic, validation, and authorization. The Vue frontend is a presentation layer. All data access goes through Laravel controllers → services → Eloquent models → Supabase PostgreSQL.

## Beginner mistakes to avoid

- Do not edit `node_modules`, `vendor`, `storage`, `bootstrap/cache`, or `src/dataconnect-generated`; these contain dependencies, runtime data, caches, or generated code.
- Do not put private Firebase service-account credentials in Vue code or a `VITE_` variable.
- Do not expose the Supabase `service_role` key in frontend code.
- Do not add an API route without considering authentication, validation, authorization, and tests.
- Restart Vite after changing frontend environment variables, and clear Laravel's configuration cache when backend environment changes appear stale.

