# Resources Folder

## Purpose and area

`resources` contains source files used to create the user-facing application. It is primarily a **frontend** folder, although `views` contains Laravel Blade templates that connect the backend response to the Vue application.

## What belongs here

- `js` contains the Vue application and browser-side services.
- `css` contains global styling and Tailwind CSS setup.
- `views` contains Blade templates rendered by Laravel.

Developers normally edit files below all three folders. Vite reads the CSS and JavaScript entry points and Laravel renders `views/app.blade.php`, which provides the element where Vue is mounted.

## Example

When adding a booking screen, create or edit Vue files under `js/pages` and `js/components`, add API logic under `js/services`, and add shared styles under `css` if needed.

## Beginner mistakes to avoid

- Do not place PHP controllers or database logic here; they belong under `app` or `database`/`dataconnect`.
- Do not put secrets in frontend resources because compiled JavaScript can be inspected by users.
- Do not edit compiled output in `public/build`; change the source here and rebuild it instead.

