# Vue Components Folder

## Purpose and area

This is a **frontend** folder for reusable Vue user-interface components. A component should represent a focused piece of the interface that can be placed inside pages or other components.

## Existing and expected files

- `LoadingSpinner.vue` is intended to show a reusable loading state.
- `AppNavbar.vue` is intended to hold shared navigation, although it is currently empty.
- Future files can include reusable buttons, booking cards, court cards, dialogs, and form fields.

Developers normally edit the `.vue` files in this folder. These components are imported by files in `pages` or by `App.vue`; they may receive data through props and report events to their parent.

## Example

When several pages need to show the same court summary, create `CourtCard.vue` here and pass the court details into it as props.

## Beginner mistakes to avoid

- Keep page-specific orchestration in `pages`; do not turn a small reusable component into an entire application screen.
- Avoid copying the same markup into multiple pages when a shared component would keep behaviour consistent.
- Do not make a component fetch unrelated data unless that responsibility is explicitly part of the component design.

