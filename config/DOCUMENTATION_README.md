# Laravel Configuration Folder

## Purpose and area

This **backend configuration** folder contains Laravel configuration arrays. These files translate environment variables into settings used throughout the application.

## Existing files developers edit

- `app.php` contains general application settings.
- `auth.php` configures Laravel authentication guards and providers.
- `database.php` defines database connections. The project uses the `pgsql` (PostgreSQL) connection to Supabase. SQLite, MySQL, MariaDB, and SQL Server connections are also defined but not active.
- `services.php` is the normal place for third-party service settings.
- `session.php`, `cache.php`, and `queue.php` configure database-backed framework features.
- `mail.php`, `logging.php`, and `filesystems.php` configure those Laravel subsystems.

Code reads these values through Laravel's `config()` helper. Actual per-machine values belong in `.env`, with safe placeholders documented in `.env.example`.

## Example

The project connects to Supabase by setting `DB_CONNECTION=pgsql` and the Supabase host, port, database, username, and password in `.env`. The `pgsql` connection in `database.php` handles the rest.

## Beginner mistakes to avoid

- Never hard-code passwords, API secrets, Supabase keys, or private keys in committed configuration files.
- Application code should read `config()` values rather than repeatedly calling `env()` outside configuration files.
- After configuration changes, stale cached settings may require `php artisan config:clear`.

