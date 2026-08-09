# Public Web Folder

## Purpose and area

This is Laravel's **public web root**. Only files in this folder should be directly reachable by a browser when the web server is configured correctly.

## Existing files developers edit

- `index.php` is Laravel's public entry point and normally requires no feature-level changes.
- `robots.txt` provides instructions to web crawlers.
- `favicon.ico` is the site's browser icon.

Vite may create a `build` output folder here for production assets. That output is generated from `resources/css` and `resources/js` and should not be manually edited or documented.

## Example

Work here when replacing the favicon or deliberately changing crawler instructions. Build Vue and CSS changes in `resources`, not here.

## Beginner mistakes to avoid

- Never place `.env`, `auth.json`, database backups, private uploads, or service-account credentials in the public folder.
- Do not modify compiled build files because a later build will replace them.
- Avoid changing `index.php` unless Laravel's request bootstrap genuinely needs adjustment.

