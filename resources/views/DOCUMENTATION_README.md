# Laravel Views Folder

## Purpose and area

This folder is the meeting point between the **Laravel backend and Vue frontend**. It contains Blade templates that Laravel renders as HTML responses.

## Existing files developers edit

- `app.blade.php` is the active Vue application shell. It loads `resources/css/app.css` and `resources/js/app.js` through Vite and provides `<div id="app">` for Vue.
- `welcome.blade.php` is Laravel's starter welcome view and is not currently returned by `routes/web.php`.

`routes/web.php` returns the `app` view for frontend URLs. After the HTML loads, Vue takes over inside the mount element.

## Example

Work here when adding global metadata, a favicon link, or server-rendered tags to the base HTML page. Build interactive screens as Vue pages rather than large Blade templates.

## Beginner mistakes to avoid

- Do not remove or rename `#app` without updating `resources/js/app.js`.
- Do not manually link compiled Vite filenames; the `@vite` directive manages development and production assets.
- Avoid implementing the same page independently in both Blade and Vue.

