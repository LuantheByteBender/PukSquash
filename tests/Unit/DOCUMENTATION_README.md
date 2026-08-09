# Unit Tests Folder

## Purpose and area

This **testing** folder contains small, fast tests for isolated PHP logic. A unit test should normally avoid the network, real Firebase services, the filesystem, and a real database.

## Existing file developers edit

`ExampleTest.php` is the starter unit test. Add focused tests here when services or value-oriented business rules can be exercised without booting the complete HTTP application.

Unit tests complement feature tests: unit tests explain a small rule precisely, while feature tests prove that routes and integrations are wired together correctly.

## Example

If booking time validation is extracted into a small class, test accepted ranges, end-before-start errors, boundary times, and overlap decisions here.

## Beginner mistakes to avoid

- Do not label a test as a unit test if it depends on a real database or Firebase network call.
- Test useful behaviour rather than private method implementation.
- Keep each test focused so a failure clearly identifies the broken rule.

