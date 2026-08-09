# Laravel Database Folder

## Purpose and area

This folder contains **Laravel SQL database development and testing** files. It describes schemas managed by Laravel migrations and provides tools for creating sample records.

## What belongs here

- `migrations` contains versioned changes to the Laravel-managed SQL schema.
- `seeders` contains repeatable initial or development data insertion.
- `factories` creates realistic test data for Laravel models.
- A local `database.sqlite` file may be used when `DB_CONNECTION=sqlite`, but database data files should not be treated as source code.

Developers normally edit PHP files in the three child folders. Laravel's settings in `config/database.php` and `.env` decide which database receives migrations and queries.

## Example

If Laravel is chosen to store a new table, generate a migration here, create its Eloquent model under `app/Models`, and optionally add a factory and seeder for testing.

## Beginner mistakes to avoid

- This folder is separate from the Firebase Data Connect PostgreSQL schema under `dataconnect`; confirm which system owns a table before creating it.
- Do not manually edit a production database to replace a migration.
- Check the active database connection before running migrations or seeders, especially with `--force`.

