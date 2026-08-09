# Laravel HTTP Folder

## Purpose and area

This **backend** folder contains code involved in Laravel's HTTP request/response lifecycle. It sits between route definitions and the rest of the application.

## What belongs here

- `Controllers` handles requests and returns responses.
- `Middleware` runs before or after controllers to enforce cross-cutting rules such as authentication.
- `Requests` holds reusable validation and authorization classes.

Developers edit the appropriate child folder when an API request needs new handling, validation, or middleware. The routes in `routes` select the controller and middleware to run.

## Example

For `POST /api/bookings`, Laravel can first run Firebase authentication middleware, then validate the submitted court and times through a form request, and finally call a controller action.

## Beginner mistakes to avoid

- Do not treat client input as safe, even if Vue already validated it.
- Return consistent HTTP status codes and JSON structures from API code.
- Avoid putting database schemas or frontend components in this folder.

