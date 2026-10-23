# Vue Components Folder

## Purpose and area

This is a **frontend** folder for reusable Vue user-interface components. A component should represent a focused piece of the interface that can be placed inside pages or other components.

## Existing components

| Component              | Purpose                                                      |
| ---------------------- | ------------------------------------------------------------ |
| `AppNavbar.vue`        | Application-wide navigation bar with role-based menu items   |
| `AppToast.vue`         | Toast notification display (driven by `useToast` composable) |
| `AvailabilityGrid.vue` | Interactive court × time-slot availability grid              |
| `BookingCard.vue`      | Card displaying a single booking's details                   |
| `BookingDialog.vue`    | Dialog for creating or joining a booking                     |
| `ConfirmDialog.vue`    | Reusable confirmation dialog                                 |
| `GridLegend.vue`       | Legend explaining the availability grid cell states           |
| `LoadingSpinner.vue`   | Reusable loading indicator                                   |

These components are imported by files in `pages/` or by `App.vue`. They receive data through props and report events to their parent.

## Example

`AvailabilityGrid.vue` is used by `BookCourtPage.vue` to render the interactive booking grid. `BookingDialog.vue` handles the booking creation flow when a grid cell is clicked.

## Beginner mistakes to avoid

- Keep page-specific orchestration in `pages`; do not turn a small reusable component into an entire application screen.
- Avoid copying the same markup into multiple pages when a shared component would keep behaviour consistent.
- Do not make a component fetch unrelated data unless that responsibility is explicitly part of the component design.

