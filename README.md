# PukSquash

PukSquash is a squash court booking system built with **Laravel 12**, **Vue 3**, **Vite**, **Tailwind CSS 4**, and **Firebase Authentication**.

- **Local development:** Uses **Supabase** (hosted PostgreSQL) as the database.
- **Production:** Uses **Firebase Data Connect** (Cloud SQL PostgreSQL) as the database.

## Features

- Court booking with availability grid
- Coach availability & private coaching
- Team practice scheduling
- Registration & PayFast payment integration
- Admin dashboard (courts, users, stats, utilisation)
- Role-based access (player, coach, admin)

## Tech Stack

| Layer      | Technology                                    |
| ---------- | --------------------------------------------- |
| Backend    | Laravel 12, PHP 8.2+                          |
| Frontend   | Vue 3, Vite 7, Tailwind CSS 4                 |
| Auth       | Firebase Authentication (client + server SDK) |
| Database (dev)  | Supabase (PostgreSQL)                    |
| Database (prod) | Firebase Data Connect (Cloud SQL PostgreSQL) |
| Payments   | PayFast                                       |
| JS Client  | @supabase/supabase-js, @supabase/ssr          |

## Requirements

- **PHP 8.2** or newer
- **Composer**
- **Node.js** and **npm**
- A **Supabase** project (free tier works for development)
- Access to the project's **Firebase** configuration and service-account credentials

## Local Setup

### 1. Clone the repository

```bash
git clone <repository-url>
cd PukSquash
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Create the environment file

**Windows PowerShell:**

```powershell
Copy-Item .env.example .env
```

**macOS / Linux:**

```bash
cp .env.example .env
```

The `.env` file is ignored by Git and must never be committed.

### 4. Generate the Laravel application key

```bash
php artisan key:generate
```

### 5. Connect to Supabase (Local Development Database)

1. Go to [supabase.com](https://supabase.com) and open your project (or create one).
2. Navigate to **Project Settings > Database**.
3. Under **Connection parameters**, copy the values and set them in `.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db.<your-project-ref>.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=<your-database-password>
```

> **Tip:** The database password is the one you chose when creating the Supabase project. You can reset it under **Project Settings > Database > Database password**.

4. *(Optional)* If you want to use the Supabase JavaScript client on the frontend, also set:

```dotenv
VITE_SUPABASE_URL=https://<your-project-ref>.supabase.co
VITE_SUPABASE_ANON_KEY=<your-anon-public-key>
```

You can find these values under **Project Settings > API** in the Supabase dashboard.

### 6. Run database migrations

```bash
php artisan migrate
```

This creates the application tables in your Supabase PostgreSQL database.

### 7. Add Firebase frontend environment values

Open `.env` and provide the Firebase web-app values from **Firebase Console > Project settings > Your apps > Web app**:

```dotenv
VITE_FIREBASE_API_KEY=
VITE_FIREBASE_AUTH_DOMAIN=
VITE_FIREBASE_PROJECT_ID=
VITE_FIREBASE_STORAGE_BUCKET=
VITE_FIREBASE_MESSAGING_SENDER_ID=
VITE_FIREBASE_APP_ID=
```

These `VITE_` values are included in the browser build and must not contain private credentials.

Restart `npm run dev` whenever these values are changed.

### 8. Add the Firebase service-account credential

Obtain the service-account JSON from the project administrator, or generate one from **Firebase Console > Project settings > Service accounts > Generate new private key**.

Save it as `auth.json` in the project root (beside `artisan`), then set in `.env`:

```dotenv
FIREBASE_CREDENTIALS=auth.json
```

`auth.json` is excluded by `.gitignore`. Never commit or share it.

### 9. Start the development servers

**Option A — Single command (recommended):**

```bash
composer dev
```

This starts Laravel, the queue worker, Pail log viewer, and Vite concurrently.

**Option B — Manual:**

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Open `http://127.0.0.1:8000` in your browser.

## Supabase Connection Reference

| `.env` Variable             | Where to Find It                                        |
| --------------------------- | ------------------------------------------------------- |
| `DB_HOST`                   | Project Settings > Database > Connection parameters     |
| `DB_PORT`                   | `5432` (default)                                        |
| `DB_DATABASE`               | `postgres` (default)                                    |
| `DB_USERNAME`               | `postgres` (default)                                    |
| `DB_PASSWORD`               | The password set when the Supabase project was created  |
| `VITE_SUPABASE_URL`         | Project Settings > API > Project URL                    |
| `VITE_SUPABASE_ANON_KEY`    | Project Settings > API > Project API keys > `anon` key  |

> **SSL:** Supabase enforces SSL by default. Laravel's `pgsql` driver uses `sslmode=prefer` which works out of the box. If you need to enforce it, add `DB_SSLMODE=require` to `.env`.

## Troubleshooting

- **Database connection refused:** Verify `DB_HOST`, `DB_PORT`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`. Ensure your IP is not blocked in Supabase under **Project Settings > Database > Network restrictions**.
- **Firebase authentication returns `401`:** Confirm `auth.json` belongs to the same Firebase project as the `VITE_FIREBASE_*` values and that `FIREBASE_CREDENTIALS=auth.json` is set in `.env`.
- **Laravel reports credential file not found:** Confirm `auth.json` is in the project root beside `artisan`.
- **Environment changes not detected:** Restart the dev server. Run `php artisan config:clear` if Laravel still uses old values.
- **No compiled assets:** Make sure `npm run dev` is running.

## Security

- Never commit `.env`, `auth.json`, or any service-account JSON file.
- If a service-account key is accidentally exposed, revoke it immediately in Firebase/Google Cloud.
- `VITE_SUPABASE_ANON_KEY` is a **public** key with Row Level Security (RLS) enforced — it is safe for the browser but should not be confused with the `service_role` key.
- Never expose the Supabase `service_role` key in frontend code or `VITE_` variables.
