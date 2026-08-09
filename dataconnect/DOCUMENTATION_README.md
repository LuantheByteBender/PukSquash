# Firebase Data Connect Folder

## Purpose and area

This folder is the project's **Firebase and PostgreSQL application-database** definition. Firebase Data Connect uses its schema and connector operations to provide authenticated access to SQL data.

## Existing files and folders developers edit

- `dataconnect.yaml` identifies the `puksquash` service, PostgreSQL database, Cloud SQL instance, region, schema source, and connector directory.
- `schema/schema.gql` defines application tables and relationships.
- `example/queries.gql` and `example/mutations.gql` define permitted operations and authentication levels.
- `seed_data.gql` is intended for Data Connect seed data and is currently empty.

`firebase.json` at the project root points Firebase tooling to this folder. The connector generates a browser SDK under `src/dataconnect-generated`, which is then referenced by `package.json`.

## Example

When adding a booking query, define the operation in `example/queries.gql`, apply an appropriate `@auth` rule, regenerate the SDK, and call the generated operation through a clear frontend service.

## Beginner mistakes to avoid

- Do not manually edit `dataconnect/.dataconnect`; it contains generated/local emulator data.
- Do not edit `src/dataconnect-generated` by hand because regeneration will overwrite it.
- Schema-level authentication and validation remain necessary even when Vue hides a button.
- Coordinate schema changes carefully because they affect PostgreSQL data and generated client types.

