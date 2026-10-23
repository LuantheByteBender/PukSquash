# Firebase Data Connect Folder (Legacy)

## ⚠️ Status: Legacy

This folder contains the **earlier Firebase Data Connect** schema and operations. **Supabase PostgreSQL** is now the primary database, managed through Laravel migrations in `database/migrations`. This folder is retained for reference but is no longer the active data layer.

## Contents

- `dataconnect.yaml` identifies the original `puksquash` service configuration.
- `schema/schema.gql` defines the original table schema (User, Court, Booking, etc.).
- `example/queries.gql` and `example/mutations.gql` define the original GraphQL operations.
- `seed_data.gql` was intended for Data Connect seed data.

## Current architecture

All data access now goes through Laravel Eloquent models → Supabase PostgreSQL. New tables and schema changes should be created as Laravel migrations in `database/migrations`, not here.

## Beginner mistakes to avoid

- Do not add new schema definitions here — use Laravel migrations instead.
- Do not rely on Data Connect operations for new features.
- The `src/dataconnect-generated` SDK is no longer actively used for data access.

