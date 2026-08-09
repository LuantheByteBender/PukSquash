# Data Connect Connector Folder

## Purpose and area

Despite the current name `example`, this folder contains the project's active **Firebase Data Connect API operations and SDK-generation configuration**.

## Existing files developers edit

- `queries.gql` defines `GetCurrentUser`, `ListCourts`, `GetCourt`, and `MyBookings`.
- `mutations.gql` defines `UpsertUser`, `CreateCourt`, and `CreateBooking`.
- `connector.yaml` identifies the connector and configures JavaScript SDK output in `src/dataconnect-generated`.

These operations sit between Vue/client code and the tables in `dataconnect/schema/schema.gql`. Their `@auth` directives control who may execute them, and client generation creates typed functions from them.

## Example

When users need to cancel their own booking, add a narrowly scoped authenticated mutation here, ensure it can only affect the caller's permitted record, then regenerate and use the client SDK.

## Beginner mistakes to avoid

- Never weaken `@auth` merely to make a failing request work; understand the required access rule.
- `CreateCourt` currently uses `NO_ACCESS`, indicating a privileged backend/admin operation rather than a normal client mutation.
- Select only the fields the client needs and keep operation variables strongly typed.
- Do not hand-edit the generated SDK after changing these files.

