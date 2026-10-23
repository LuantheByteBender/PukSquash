# Laravel Migrations Folder

## Purpose and area

This **Laravel backend** folder contains ordered, version-controlled schema changes that run against the Supabase PostgreSQL database. Laravel uses migration filenames to determine their execution order.

## Existing migrations

- `0001_01_01_000000_create_users_table.php` creates the users table, password-reset tokens, and sessions.
- `0001_01_01_000001_create_cache_table.php` creates cache and cache-lock tables.
- `0001_01_01_000002_create_jobs_table.php` creates queue and failed-job tables.
- `2026_09_28_000003_add_supabase_performance_indexes.php` adds performance indexes for Supabase PostgreSQL queries.

Run `php artisan migrate` to apply pending migrations to Supabase. New schema changes should be placed in a new migration rather than rewriting one that teammates or deployed environments may already have executed.

## Example

If the `users` table needs a new column, generate a new migration with `php artisan make:migration add_column_to_users_table` that adds the column and provides a safe reversal in `down()`.

## Beginner mistakes to avoid

- Never assume rollback is harmless; it can delete columns or data.
- Add indexes, foreign keys, nullability, and deletion behaviour deliberately rather than relying on guesses.
- Test migrations against a development Supabase instance before applying to production.

