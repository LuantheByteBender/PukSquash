# Laravel Configuration Folder

## Purpose and area

This **backend configuration** folder contains Laravel configuration arrays. These files translate environment variables into settings used throughout the application.

## Existing files developers edit

- `app.php` contains general application settings.
- `auth.php` configures Laravel authentication guards and providers.
- `database.php` defines SQLite, MySQL, MariaDB, PostgreSQL, SQL Server, and Redis connections.
- `services.php` is the normal place for third-party service settings.
- `session.php`, `cache.php`, and `queue.php` configure database-backed framework features used by the current `.env.example`.
- `mail.php`, `logging.php`, and `filesystems.php` configure those Laravel subsystems.

Code reads these values through Laravel's `config()` helper. Actual per-machine values belong in `.env`, with safe placeholders documented in `.env.example`.

## Example

When changing Laravel from SQLite to a supported SQL server, set the appropriate `DB_*` variables in `.env` and only adjust `database.php` if the existing connection options are insufficient.

## Beginner mistakes to avoid

- Never hard-code passwords, API secrets, or private keys in committed configuration files.
- Application code should read `config()` values rather than repeatedly calling `env()` outside configuration files.
- After configuration changes, stale cached settings may require `php artisan config:clear`.

