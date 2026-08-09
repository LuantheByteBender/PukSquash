# PukSquash Developer Guide

This repository combines a Vue frontend, a Laravel backend, Firebase Authentication, and SQL database definitions. Start here when you are unsure where a change belongs.

## Where each part lives

- **Frontend:** `resources/js` contains Vue components, pages, authentication features, and API services. `resources/css` contains the global Tailwind stylesheet, and `resources/views/app.blade.php` provides the HTML page in which Vue runs.
- **Backend:** `app` contains Laravel models, middleware, services, controllers, and request validation. `routes` defines the URLs handled by Laravel.
- **Database:** `dataconnect` contains the Firebase Data Connect PostgreSQL schema and operations. `database` contains Laravel migrations, seeders, and factories used by Laravel's own SQL connection.
- **Firebase:** Browser configuration is in `resources/js/services/firebase.js`; backend token verification is in `app/Services/FirebaseAuthService.php` and `app/Http/Middleware/FirebaseAuth.php`; Data Connect configuration is under `dataconnect`.
- **Configuration:** `.env` supplies local settings, while `.env.example`, `config`, `firebase.json`, `vite.config.js`, `package.json`, and `composer.json` describe application and tool configuration.
- **Testing:** `tests/Feature` is for behaviour spanning several parts of Laravel, while `tests/Unit` is for small isolated pieces.

## Good folders for a beginner to inspect first

1. Read `resources/js/App.vue` to see which Vue page is currently displayed.
2. Read `resources/js/pages/AuthTest.vue` to see the current login and backend-authentication flow.
3. Read `routes/api.php` to see the currently available API endpoint.
4. Read `app/Http/Middleware/FirebaseAuth.php` to understand how Laravel verifies Firebase tokens.
5. Read `dataconnect/schema/schema.gql` to understand the `User`, `Court`, and `Booking` tables.

## Simplified project tree

```text
PukSquash/
|-- app/                         Laravel backend code
|   |-- Http/                    Controllers, middleware, and validation
|   |-- Models/                  Laravel Eloquent models
|   `-- Services/                Reusable backend services
|-- config/                      Laravel configuration
|-- database/                    Laravel migrations, factories, and seeders
|-- dataconnect/                 PostgreSQL schema, queries, and mutations
|-- public/                      Public web entry point and static files
|-- resources/
|   |-- css/                     Global frontend styles
|   |-- js/                      Vue application
|   `-- views/                   Laravel Blade HTML shells
|-- routes/                      Web, API, and console route definitions
|-- tests/                       Automated PHP tests
|-- .env.example                 Safe environment-variable template
|-- firebase.json                Firebase/Data Connect emulator configuration
|-- package.json                 JavaScript dependencies and scripts
|-- composer.json                PHP dependencies and scripts
`-- vite.config.js               Vue/Vite build configuration
```

## Firebase credentials and environment variables

The Vue application reads `VITE_FIREBASE_API_KEY`, `VITE_FIREBASE_AUTH_DOMAIN`, `VITE_FIREBASE_PROJECT_ID`, `VITE_FIREBASE_STORAGE_BUCKET`, `VITE_FIREBASE_MESSAGING_SENDER_ID`, and `VITE_FIREBASE_APP_ID`. Laravel reads `FIREBASE_CREDENTIALS`, which points to a private service-account JSON file such as `auth.json`.

Never commit `.env`, `auth.json`, private keys, database passwords, or other service-account JSON files. Variables beginning with `VITE_` are included in the browser build and therefore cannot safely contain server secrets.

## Important architecture note

The project currently has two SQL-related areas. Firebase Data Connect defines the PukSquash application entities (`User`, `Court`, and `Booking`) in PostgreSQL. Laravel's `database` folder still contains its starter users, sessions, cache, and queue migrations. Before adding a new application table, confirm which database layer owns it so that the same concept is not implemented twice.

## Beginner mistakes to avoid

- Do not edit `node_modules`, `vendor`, `storage`, `bootstrap/cache`, `dataconnect/.dataconnect`, or `src/dataconnect-generated`; these contain dependencies, runtime data, caches, or generated code.
- Do not put private Firebase service-account credentials in Vue code or a `VITE_` variable.
- Do not add an API route without considering authentication, validation, authorization, and tests.
- Do not edit generated Data Connect client files manually; update the schema or connector operations and regenerate the client.
- Restart Vite after changing frontend environment variables, and clear Laravel's configuration cache when backend environment changes appear stale.

