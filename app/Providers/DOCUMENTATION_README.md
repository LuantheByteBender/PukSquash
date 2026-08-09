# Laravel Providers Folder

## Purpose and area

This **Laravel backend configuration/bootstrap** folder contains service providers. Providers register application services with Laravel's container and run application-wide boot logic.

## Existing file developers edit

`AppServiceProvider.php` is the project's general-purpose provider. It currently contains the standard empty `register` and `boot` methods. Laravel loads application providers listed through `bootstrap/providers.php`.

Work here when a real application-wide binding, macro, policy registration, or boot-time customization is required.

## Example

If an interface is introduced so controllers can use different booking repositories, bind that interface to its implementation in a provider.

## Beginner mistakes to avoid

- Do not put ordinary request handling or page-specific logic here; providers run as part of application startup.
- Heavy database or network work during every application boot can slow all requests.
- Avoid changing providers simply because a class can already be resolved automatically by Laravel.

