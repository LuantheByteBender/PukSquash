# Automated Tests Folder

## Purpose and area

This folder contains **backend automated tests** run by PHPUnit through Laravel. Tests protect expected behaviour and make future changes safer.

## What belongs here

- `TestCase.php` is the shared Laravel test base class.
- `Feature` contains tests that exercise routes, middleware, databases, or several classes together.
- `Unit` contains small isolated tests for individual rules or classes.
- `phpunit.xml` at the repository root configures the test environment.

Developers normally add or update tests whenever they change backend behaviour. Run them with `php artisan test` or the Composer test script.

## Example

When adding a protected booking endpoint, test missing and invalid Firebase tokens, invalid booking input, successful creation, authorization rules, and scheduling conflicts.

## Beginner mistakes to avoid

- Tests must not call production databases or modify real Firebase users.
- Keep test setup deterministic; do not depend on execution order or leftover data.
- An example test proving the framework boots is not enough coverage for application rules.

