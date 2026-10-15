<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserFundsRepository
{
    public const COURT_BOOKING_FEE_CENTS = 2500;

    public function addFunds(string $userId, int $amountCents): int
    {
        return DB::transaction(function () use ($userId, $amountCents): int {
            $user = $this->lockedUser($userId);
            $balance = (int) ($user->funds_balance_cents ?? 0) + $amountCents;

            DB::table('users')
                ->where('id', $userId)
                ->update([
                    'funds_balance_cents' => $balance,
                    'updated_at' => now(),
                ]);

            $this->forgetCachedUser($user);

            return $balance;
        });
    }

    public function deductFunds(string $userId, int $amountCents): int
    {
        $user = $this->lockedUser($userId);
        $balance = (int) ($user->funds_balance_cents ?? 0);

        if ($balance < $amountCents) {
            throw new \RuntimeException('INSUFFICIENT_FUNDS');
        }

        $nextBalance = $balance - $amountCents;

        DB::table('users')
            ->where('id', $userId)
            ->update([
                'funds_balance_cents' => $nextBalance,
                'updated_at' => now(),
            ]);

        $this->forgetCachedUser($user);

        return $nextBalance;
    }

    public function updatePrivateCoachingFee(string $coachId, int $amountCents): int
    {
        return DB::transaction(function () use ($coachId, $amountCents): int {
            $user = $this->lockedUser($coachId);

            DB::table('users')
                ->where('id', $coachId)
                ->update([
                    'private_coaching_fee_cents' => $amountCents,
                    'updated_at' => now(),
                ]);

            $this->forgetCachedUser($user);

            return $amountCents;
        });
    }

    public function privateCoachingFeeForCoach(string $coachId): int
    {
        $this->assertFundsColumnsExist();

        return (int) (DB::table('users')
            ->where('id', $coachId)
            ->value('private_coaching_fee_cents') ?? 0);
    }

    private function lockedUser(string $userId): object
    {
        $this->assertFundsColumnsExist();

        $user = DB::table('users')
            ->where('id', $userId)
            ->lockForUpdate()
            ->first();

        if (! $user) {
            throw new \RuntimeException('USER_NOT_FOUND');
        }

        return $user;
    }

    private function forgetCachedUser(object $user): void
    {
        Cache::forget('sqlconnect_user_id_' . (string) $user->id);
        Cache::forget('sqlconnect_user_firebase_uid_' . (string) ($user->firebase_uid ?? ''));
    }

    private function assertFundsColumnsExist(): void
    {
        if (! Schema::hasColumn('users', 'funds_balance_cents') || ! Schema::hasColumn('users', 'private_coaching_fee_cents')) {
            throw new \RuntimeException('FUNDS_COLUMNS_MISSING');
        }
    }
}
