# Laravel Database Folder

## Purpose and area

This folder contains **Laravel database development and testing** files. It describes schemas managed by Laravel migrations against the Supabase PostgreSQL database and provides tools for creating sample records.

## What belongs here

- `migrations` contains versioned changes to the database schema (run against Supabase PostgreSQL).
- `seeders` contains repeatable initial or development data insertion.
- `factories` creates realistic test data for Laravel models.

Developers normally edit PHP files in the three child folders. The database connection is configured via `DB_CONNECTION=pgsql` and Supabase credentials in `.env`.

## Example

When adding a new table, generate a migration here with `php artisan make:migration`, create its Eloquent model under `app/Models`, and optionally add a factory and seeder for testing. Run `php artisan migrate` to apply it to Supabase.

## Beginner mistakes to avoid

- Do not manually edit the Supabase database to replace a migration.
- Check the active database connection before running migrations or seeders, especially with `--force`.
- Always test migrations locally or in a staging environment before running against production.

