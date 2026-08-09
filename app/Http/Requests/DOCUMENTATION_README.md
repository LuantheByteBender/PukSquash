# Laravel Request Validation Folder

## Purpose and area

This existing **backend validation** folder is intended for Laravel Form Request classes. It is currently empty apart from this guide.

## What belongs here

Place classes here that define validation rules and, where appropriate, authorization checks for incoming requests. Controllers can type-hint these classes and receive already validated data. Examples might include booking times, court identifiers, or user-profile fields once those endpoints exist.

Request classes connect `routes` and controllers to Laravel's validation system. Developers edit them when an endpoint's accepted input or authorization rule changes.

## Example

A future `StoreBookingRequest` could require a valid court ID, parseable start and end times, and an end time after the start time before the controller attempts a booking.

## Beginner mistakes to avoid

- Frontend validation improves usability but never replaces validation here or in the database layer.
- Do not silently accept fields that the application does not use.
- Validation checks data shape; business rules such as court availability may require a service or database transaction as well.

