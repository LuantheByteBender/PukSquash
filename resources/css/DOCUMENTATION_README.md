# Frontend CSS Folder

## Purpose and area

This is the **frontend styling** folder. It contains CSS that Vite compiles and makes available to the Vue application.

## Existing file developers edit

`app.css` is the global stylesheet and imports Tailwind CSS. Its `@source` declarations tell Tailwind where to look for class names, and its `@theme` block defines shared theme values such as the default font.

The file is listed as a Vite entry point in `vite.config.js` and is loaded through `resources/views/app.blade.php`. Component-specific styles may also live inside a Vue component's `<style>` block when they are not global.

## Example

When the whole application needs a common brand colour or typography token, define it in `app.css`; when only one card needs a private style, keep that style close to its Vue component.

## Beginner mistakes to avoid

- Do not edit generated CSS under `public/build`.
- Be careful when changing Tailwind `@source` paths; missing paths can cause expected utility classes not to be generated.
- Avoid placing secrets or application data in CSS.

