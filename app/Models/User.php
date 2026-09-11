<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @deprecated This Eloquent model is retained only so that Laravel's internal
 *             auth scaffolding does not break. The application does NOT use
 *             Eloquent or migrations for user data — the source of truth is the
 *             Firebase SQL Connect PostgreSQL database, accessed through
 *             App\Services\SqlConnectClient and App\Repositories\UserRepository.
 *
 *             Do not add business logic, scopes, or relationships here.
 */
class User extends Authenticatable
{
}