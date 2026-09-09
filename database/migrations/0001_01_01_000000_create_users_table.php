<?php

use Illuminate\Database\Migrations\Migration;

/**
 * @deprecated USERS table is managed by Firebase SQL Connect, not Laravel
 *             migrations. This file is a no-op to prevent artisan migrate from
 *             creating a local users table that conflicts with the SQL Connect
 *             schema. See dataconnect/schema/schema.gql for the source of truth.
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op — users, password_reset_tokens and sessions are not used.
        // User data lives in the SQL Connect PostgreSQL database.
    }

    public function down(): void
    {
        // No-op.
    }
};
