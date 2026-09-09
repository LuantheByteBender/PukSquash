<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @deprecated User data is managed by Firebase SQL Connect. This factory is
 *             retained only so that Laravel's internal references do not break.
 *             Do not use it for tests — test data should be seeded via the
 *             SqlConnectClient or the Data Connect emulator.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [];
    }
}
