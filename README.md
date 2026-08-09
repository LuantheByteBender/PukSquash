# PukSquash

PukSquash is a Laravel 12 application with a Vue frontend, Vite, Tailwind CSS, and Firebase Authentication.

## Requirements

Install the following before setting up the project:

- PHP 8.2 or newer
- Composer
- Node.js and npm
- Access to the project's Firebase configuration and service-account credentials

## Local setup

### 1. Clone the repository

```bash
git clone <repository-url>
cd PukSquash
```

Replace `<repository-url>` with the URL of this repository. Make sure the terminal is in the directory containing `artisan`, `composer.json`, and `package.json` before continuing.

### 2. Install PHP dependencies

```bash
composer install
```

This installs Laravel and the PHP Firebase SDK into the `vendor` directory.

### 3. Install frontend dependencies

```bash
npm install
```

This installs Vue, Vite, Tailwind CSS, Firebase's JavaScript SDK, and the other frontend packages into `node_modules`.

### 4. Create the local environment file

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

On macOS or Linux:

```bash
cp .env.example .env
```

The `.env` file contains machine-specific configuration and secrets. It is ignored by Git and must not be committed.

### 5. Generate the Laravel application key

```bash
php artisan key:generate
```

This writes the `APP_KEY` value to `.env`. Laravel uses it to encrypt application data.

### 6. Add the Firebase frontend environment values

Open `.env` and provide the Firebase web-app values supplied by the project administrator or found in **Firebase Console > Project settings > Your apps > Web app**:

```dotenv
VITE_FIREBASE_API_KEY=
VITE_FIREBASE_AUTH_DOMAIN=
VITE_FIREBASE_PROJECT_ID=
VITE_FIREBASE_STORAGE_BUCKET=
VITE_FIREBASE_MESSAGING_SENDER_ID=
VITE_FIREBASE_APP_ID=
```

These `VITE_` values are used by the Vue frontend. Values prefixed with `VITE_` are included in the browser build and must not contain private service-account credentials.

Restart `npm run dev` whenever these values are changed.

### 7. Add the local Firebase service-account credential

Obtain the authorized Firebase service-account JSON file from the project administrator. Alternatively, an authorized Firebase administrator can generate one from **Firebase Console > Project settings > Service accounts > Generate new private key**.

Save the downloaded file as:

```text
auth.json
```

Place it in the project root, beside `artisan`. Then set this value in `.env`:

```dotenv
FIREBASE_CREDENTIALS=auth.json
```

`auth.json` contains a private key and is already excluded by `.gitignore`. Never commit it, send it through an insecure channel, or expose its contents in a frontend `VITE_` variable.

### 8. Start the Laravel backend

In the first terminal, run:

```bash
php artisan serve
```

By default, Laravel starts at `http://127.0.0.1:8000`.

### 9. Start the Vite frontend development server

Keep Laravel running. Open a second terminal in the project root and run:

```bash
npm run dev
```

Vite compiles the Vue frontend and automatically refreshes it when frontend files change. Open the Laravel URL shown by `php artisan serve`, normally `http://127.0.0.1:8000`.



## Troubleshooting

- **Firebase authentication returns `401`:** Confirm that `auth.json` belongs to the same Firebase project as the `VITE_FIREBASE_*` values and that `FIREBASE_CREDENTIALS=auth.json` is present in `.env`.
- **Laravel reports that the credential file cannot be found:** Confirm that `auth.json` is beside `artisan`, not inside `resources` or `public`.
- **Environment changes are not detected:** Restart the relevant development server. If Laravel still uses old values, run `php artisan config:clear`.
- **The page has no compiled assets:** Make sure `npm run dev` is still running in a second terminal.

## Security

Do not commit `.env`, `auth.json`, or any other service-account JSON file. If a service-account key is accidentally exposed, revoke it immediately in Firebase/Google Cloud and generate a replacement.
