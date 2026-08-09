# Data Connect Schema Folder

## Purpose and area

This is the **Firebase Data Connect and PostgreSQL database-schema** folder. It is the source of truth for application tables managed through Data Connect.

## Existing file developers edit

`schema.gql` currently defines:

- `User`, keyed by the Firebase Authentication UID, with student, profile, role, and status fields.
- `Court`, representing a squash court and its availability status.
- `Booking`, relating a user to a court for a start and end time.

Data Connect turns these GraphQL-style table definitions into PostgreSQL structures. Operations in `dataconnect/example` select or modify the fields defined here, and SDK generation exposes those operations to JavaScript.

## Example

When a booking needs a new cancellation reason, update the schema deliberately, update the relevant queries and mutations, deploy/apply the schema change, and regenerate the client SDK.

## Beginner mistakes to avoid

- Do not use free-form status strings without considering validation and allowed values.
- Relationships, uniqueness rules, required fields, defaults, and indexes affect data integrity; review them before deployment.
- A schema change can break existing operations or data, so update all consumers and test migrations carefully.
- Do not add Firebase service-account secrets to schema files.

