# Laravel Seeders Folder

## Purpose and area

This **Laravel database and development/testing** folder contains code that inserts known data into Laravel's configured SQL database.

## Existing file developers edit

`DatabaseSeeder.php` is the main seeder. It currently creates one Laravel `User` through `UserFactory`. Other seeders can be called from this file when real Laravel-managed datasets are introduced.

Seeders use models and factories to populate tables after migrations. Run them only against the intended environment and database.

## Example

For local testing of a Laravel-managed feature, add a small predictable dataset and call its seeder from `DatabaseSeeder` so another developer can reproduce the setup.

## Beginner mistakes to avoid

- Do not place real passwords, personal information, or production secrets in seed data.
- Seeders may duplicate or overwrite data if they are not designed to be safely rerun.
- The empty `dataconnect/seed_data.gql` file is the separate seed location for Data Connect; Laravel seeders do not automatically populate it.

