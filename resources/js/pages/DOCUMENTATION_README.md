# Vue Pages Folder

## Purpose and area

This **frontend** folder contains page-sized Vue components. Each page is a screen in the application, rendered by Vue Router based on the route defined in `router/index.js`.

## Existing pages

| Page                            | Route              | Purpose                                    |
| ------------------------------- | ------------------ | ------------------------------------------ |
| `LandingPage.vue`               | `/`                | Public landing page                        |
| `LoginPage.vue`                 | `/login`           | User login                                 |
| `SignUpPage.vue`                 | `/signup`          | User registration                          |
| `RegistrationPaymentPage.vue`   | `/register/pay`    | Registration payment flow                  |
| `HomePage.vue`                  | `/home`            | Authenticated home dashboard               |
| `BookCourtPage.vue`             | `/book`            | Court booking with availability grid       |
| `MyBookingsPage.vue`            | `/my-bookings`     | List of user's own bookings                |
| `CoachProfile.vue`              | `/coach/profile`   | Coach profile management                   |
| `CoachBookings.vue`             | `/coach/bookings`  | Coach's upcoming bookings                  |
| `CoachNoticeBoardPage.vue`      | `/coach/notice-board` | Coach availability notice board         |
| `CoachTeamPracticeBookingPage.vue` | `/coach/team-practice` | Team practice booking              |
| `AdminDashboard.vue`            | `/admin`           | Admin stats and utilisation dashboard      |
| `ManageUsersPage.vue`           | `/admin/users`     | Admin user management                      |
| `ManageCourtsPage.vue`          | `/admin/courts`    | Admin court management                     |
| `PaymentReturnPage.vue`         | `/payment/return`  | Payment success return                     |
| `PaymentCancelPage.vue`         | `/payment/cancel`  | Payment cancellation                       |
| `AuthTest.vue`                  | `/auth-test`       | Development auth testing page              |

Pages connect to `features` for feature behaviour, `services` for API calls, and `components` for reusable presentation.

## Example

When adding a new screen, create a Vue component here, add a route in `router/index.js`, and use `services/http.js` or a feature service to fetch data from the Laravel API.

## Beginner mistakes to avoid

- Do not put private credentials or backend-only validation in a page.
- Do not assume frontend validation is sufficient; Laravel must still enforce security rules.
- Avoid putting every reusable element directly in a page; move repeated interface pieces to `components`.

