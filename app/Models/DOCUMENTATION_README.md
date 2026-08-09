# Laravel Models Folder

## Purpose and area

This **Laravel backend and SQL database** folder contains Eloquent models. A model represents data stored through Laravel's configured database connection and can define attributes, type conversions, and relationships.

## Existing file developers edit

`User.php` is Laravel's starter user model and corresponds to the Laravel `users` migration. It defines assignable fields, hidden fields, and value casts.

Be aware that `dataconnect/schema/schema.gql` separately defines a Firebase Data Connect `User` table keyed by a Firebase UID. Before extending `User.php`, confirm whether the feature uses Laravel's SQL tables or Data Connect PostgreSQL.

## Example

If Laravel is deliberately chosen to own a new SQL entity, add its model here and its schema migration under `database/migrations`, then define relationships on the relevant models.

## Beginner mistakes to avoid

- Do not assume this `User` model automatically represents Firebase Authentication or the Data Connect `User` table.
- Update mass-assignment rules carefully; adding sensitive fields to `$fillable` can create security problems.
- A model change does not alter the database schema—use the appropriate migration or Data Connect schema definition too.

