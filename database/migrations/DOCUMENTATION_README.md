# Laravel Migrations Folder

## Purpose and area

This **Laravel backend and SQL database** folder contains ordered, version-controlled schema changes. Laravel uses migration filenames to determine their execution order.

## Existing files developers edit

- `0001_01_01_000000_create_users_table.php` creates Laravel users, password-reset tokens, and sessions.
- `0001_01_01_000001_create_cache_table.php` creates cache and cache-lock tables.
- `0001_01_01_000002_create_jobs_table.php` creates queue and failed-job tables.

Migrations connect Eloquent models and Laravel framework features to the configured database in `config/database.php`. New schema changes should normally be placed in a new migration rather than rewriting a migration that teammates or deployed environments may already have executed.

## Example

If Laravel needs a new column on its own `users` table, generate a new migration that adds the column and provides a safe reversal in `down()`.

## Beginner mistakes to avoid

- Do not create Laravel migrations for tables owned by `dataconnect/schema/schema.gql` unless the architecture deliberately requires separate tables.
- Never assume rollback is harmless; it can delete columns or data.
- Add indexes, foreign keys, nullability, and deletion behaviour deliberately rather than relying on guesses.

