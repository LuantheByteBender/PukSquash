# Frontend Features Folder

## Purpose and area

This is a **frontend** organization folder. It groups code by product feature so that related behaviour does not become scattered across generic utility files.

## What belongs here

The existing `auth` subfolder contains authentication behaviour. Future feature subfolders should only be added when the corresponding feature exists, for example after booking or court-management work begins. Feature code may call `services` and be used by `pages` and `components`.

Developers normally edit files inside the relevant feature subfolder rather than this documentation file.

## Example

When authentication behaviour changes, work in `features/auth` and keep the Firebase initialization itself in `services/firebase.js`.

## Beginner mistakes to avoid

- Do not create empty feature folders merely to predict future needs.
- Avoid duplicating generic API or Firebase initialization code inside every feature.
- Keep backend authorization in Laravel or Data Connect as well; organizing frontend features does not create a security boundary.

