# API Controllers Folder

## Purpose and area

This existing **Laravel backend API** folder is reserved for controllers that return data-oriented responses, usually JSON, to the Vue frontend or another client. It is currently empty apart from this guide.

## What belongs here

Place API controller classes here when endpoints become substantial enough to move out of route closures. Developers will normally edit those controller classes and connect them to endpoints in `routes/api.php`.

These controllers can receive validated request objects from `app/Http/Requests`, use authenticated Firebase information added by middleware, and call services or the chosen database layer.

## Example

When `/api/me` grows beyond its current small closure, create an API controller action here and update `routes/api.php` to reference that action.

## Beginner mistakes to avoid

- Do not create a controller until there is an actual route or feature needing it.
- API controllers should normally return JSON, not the Vue application's Blade shell.
- Protect sensitive actions with authentication and role-based authorization.

