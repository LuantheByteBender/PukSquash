# Laravel Factories Folder

## Purpose and area

This **Laravel database testing** folder contains model factories. Factories generate realistic, disposable records for automated tests, local development, and seeders.

## Existing file developers edit

`UserFactory.php` creates values for Laravel's `App\Models\User`, including a unique fake email and a safely hashed default test password. Its `unverified()` state creates an unverified Laravel user.

Factories connect Laravel models to seeders and tests. They describe test data, not the production schema; migrations still define the actual columns.

## Example

A feature test can call `User::factory()->create()` to arrange a user record without manually specifying every field.

## Beginner mistakes to avoid

- Never use real personal data or production credentials in a factory.
- Keep factory fields aligned with the corresponding migration and model.
- This Laravel user factory does not create a Firebase Authentication account automatically — it only creates the database record.

