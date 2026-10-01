<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RegistrationPaymentRepository
{
    public function findByMerchantPaymentIdForUpdate(string $merchantPaymentId): ?object
    {
        return DB::table('registration_payments')
            ->where('merchant_payment_id', $merchantPaymentId)
            ->lockForUpdate()
            ->first();
    }

    public function findByMerchantPaymentId(string $merchantPaymentId): ?object
    {
        return DB::table('registration_payments')
            ->where('merchant_payment_id', $merchantPaymentId)
            ->first();
    }

    public function findLatestForPendingRegistration(string $pendingRegistrationId): ?object
    {
        return DB::table('registration_payments')
            ->where('pending_registration_id', $pendingRegistrationId)
            ->orderByDesc('created_at')
            ->first();
    }

    public function createPending(
        string $pendingRegistrationId,
        string $merchantPaymentId,
        int $amountCents,
        string $currency = 'ZAR',
        string $provider = 'PAYFAST',
    ): object {
        $now = now();

        $id = (string) Str::uuid();

        $insert = [
            'id' => $id,
            'pending_registration_id' => $pendingRegistrationId,
            'merchant_payment_id' => $merchantPaymentId,
            'provider' => $provider,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'status' => 'PENDING',
            'created_at' => $now,
        ];

        DB::table('registration_payments')->insert($insert);

        return DB::table('registration_payments')->where('id', $id)->first();
    }

    public function markPaid(string $paymentId, ?string $payfastPaymentId = null): void
    {
        DB::table('registration_payments')
            ->where('id', $paymentId)
            ->update([
                'status' => 'PAID',
                'payfast_payment_id' => $payfastPaymentId,
                'paid_at' => now(),
            ]);
    }

    public function markFailed(string $paymentId, ?string $payfastPaymentId = null): void
    {
        DB::table('registration_payments')
            ->where('id', $paymentId)
            ->update([
                'status' => 'FAILED',
                'payfast_payment_id' => $payfastPaymentId,
            ]);
    }

    public function markCancelled(string $paymentId): void
    {
        DB::table('registration_payments')
            ->where('id', $paymentId)
            ->update([
                'status' => 'CANCELLED',
            ]);
    }
}
