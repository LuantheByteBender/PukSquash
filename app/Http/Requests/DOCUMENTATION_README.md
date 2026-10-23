# Laravel Request Validation Folder

## Purpose and area

This **backend validation** folder contains Laravel Form Request classes. Controllers type-hint these classes and receive already validated data.

## Existing files

- `PublishCoachAvailabilityRequest.php` validates coach availability publishing (date, time slot, and availability rules).

Add new Form Request classes here when endpoints need structured validation for incoming data such as booking details, court management, or user-profile updates.

## Example

`PublishCoachAvailabilityRequest` ensures the submitted date and time-slot data is valid before `CoachAvailabilityController` processes it.

## Beginner mistakes to avoid

- Frontend validation improves usability but never replaces validation here or in the database layer.
- Do not silently accept fields that the application does not use.
- Validation checks data shape; business rules such as court availability may require a service or database transaction as well.

