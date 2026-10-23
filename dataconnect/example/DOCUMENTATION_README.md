# Data Connect Connector Folder (Legacy)

## ⚠️ Status: Legacy

Despite the name `example`, this folder contained the project's **Firebase Data Connect API operations and SDK-generation configuration**. **All data access now goes through the Laravel REST API and Eloquent models connected to Supabase PostgreSQL.** This folder is retained for reference only.

## Original contents

- `queries.gql` defined `GetCurrentUser`, `ListCourts`, `GetCourt`, and `MyBookings`.
- `mutations.gql` defined `UpsertUser`, `CreateCourt`, and `CreateBooking`.
- `connector.yaml` identified the connector and configured JavaScript SDK output.

## Current approach

New API operations should be added as Laravel controller actions in `app/Http/Controllers/Api/` and routes in `routes/api.php`, using Eloquent models for data access.

## Beginner mistakes to avoid

- Do not add new operations here — use Laravel controllers and Eloquent instead.
- Do not hand-edit the generated SDK in `src/dataconnect-generated`.

