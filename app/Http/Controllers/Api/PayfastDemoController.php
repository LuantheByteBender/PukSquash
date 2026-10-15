<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\PendingRegistrationRepository;
use App\Repositories\RegistrationPaymentRepository;
use App\Repositories\UserRepository;
use App\Services\FirebaseAuthService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayfastDemoController extends Controller
{
    public function __construct(
        private RegistrationPaymentRepository $payments,
        private PendingRegistrationRepository $pendingRegistrations,
        private UserRepository $users,
        private FirebaseAuthService $firebase,
    ) {
    }

    public function completeRegistrationPayment(Request $request): JsonResponse
    {
        $sandbox = (bool) config('payfast.sandbox');

        if (! $sandbox) {
            return response()->json([
                'message' => 'Not found.',
            ], 404);
        }

        $firebaseUid = (string) $request->attributes->get('firebase_uid');

        $user = $this->users->findByFirebaseUid($firebaseUid);
        if ($user) {
            return response()->json([
                'registered' => true,
            ]);
        }

        $pending = $this->pendingRegistrations->findByFirebaseUid($firebaseUid);
        if (! $pending) {
            return response()->json([
                'registered' => false,
                'message' => 'No pending registration found.',
                'firebase_uid' => $firebaseUid,
            ], 404);
        }

        $latest = $this->payments->findLatestForPendingRegistration((string) $pending->id);
        if (! $latest) {
            return response()->json([
                'registered' => false,
                'message' => 'No payment found.',
                'pending_registration_id' => (string) $pending->id,
            ], 409);
        }

        $beforeStatus = (string) ($latest->status ?? '');

        $merchantPaymentId = (string) ($latest->merchant_payment_id ?? '');
        if ($merchantPaymentId === '') {
            return response()->json([
                'registered' => false,
                'message' => 'Invalid payment reference.',
                'registration_payment_id' => (string) ($latest->id ?? ''),
            ], 409);
        }

        $payfastPaymentId = 'DEMO-' . Str::uuid()->toString();

        DB::transaction(function () use ($latest, $pending, $payfastPaymentId): void {
            $paidAlready = DB::table('registration_payments')
                ->where('pending_registration_id', (string) ($pending->id ?? ''))
                ->where('status', 'PAID')
                ->lockForUpdate()
                ->exists();

            if ($paidAlready) {
                return;
            }

            $payment = DB::table('registration_payments')
                ->where('id', (string) ($latest->id ?? ''))
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return;
            }

            if ((string) ($payment->status ?? '') !== 'PAID') {
                try {
                    $this->payments->markPaid((string) $payment->id, $payfastPaymentId);
                } catch (QueryException $e) {
                    $sqlState = $e->errorInfo[0] ?? null;
                    $message = $e->getMessage();

                    if ($sqlState === '23505' && is_string($message) && str_contains($message, 'registration_payments_one_paid')) {
                        return;
                    }

                    throw $e;
                }
            }
        });

        $this->attemptCompletePayment($merchantPaymentId, $payfastPaymentId);

        $latestAfter = $this->payments->findLatestForPendingRegistration((string) $pending->id);
        $afterStatus = $latestAfter ? (string) ($latestAfter->status ?? '') : null;

        $user = $this->users->findByFirebaseUid($firebaseUid);

        return response()->json([
            'registered' => (bool) $user,
            'pending_registration_id' => (string) $pending->id,
            'registration_payment_id' => (string) ($latest->id ?? ''),
            'status_before' => $beforeStatus,
            'status_after' => $afterStatus,
        ]);
    }

    private function attemptCompletePayment(string $merchantPaymentId, string $payfastPaymentId): void
    {
        try {
            DB::transaction(function () use ($merchantPaymentId, $payfastPaymentId): void {
                $payment = $this->payments->findByMerchantPaymentIdForUpdate($merchantPaymentId);
                if (! $payment) {
                    return;
                }

                $pending = DB::table('pending_registrations')
                    ->where('id', (string) $payment->pending_registration_id)
                    ->lockForUpdate()
                    ->first();

                if (! $pending) {
                    return;
                }

                try {
                    $firebaseUser = $this->firebase->getUser((string) $pending->firebase_uid);
                } catch (\Throwable) {
                    try {
                        $this->payments->markPaid((string) $payment->id, $payfastPaymentId);
                    } catch (\Throwable) {
                        $this->payments->markFailed((string) $payment->id, $payfastPaymentId);
                    }

                    return;
                }

                if ($firebaseUser->emailVerified !== true) {
                    try {
                        $this->payments->markPaid((string) $payment->id, $payfastPaymentId);
                    } catch (\Throwable) {
                        $this->payments->markFailed((string) $payment->id, $payfastPaymentId);
                    }

                    return;
                }

                if ($payment->status !== 'PAID') {
                    $alreadyPaid = DB::table('registration_payments')
                        ->where('pending_registration_id', (string) $payment->pending_registration_id)
                        ->where('status', 'PAID')
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyPaid) {
                        return;
                    }

                    try {
                        $this->payments->markPaid((string) $payment->id, $payfastPaymentId);
                    } catch (\Throwable) {
                        $this->payments->markFailed((string) $payment->id, $payfastPaymentId);

                        return;
                    }
                }

                if (! $this->users->findByFirebaseUid((string) $pending->firebase_uid)) {
                    $this->pendingRegistrations->markEmailVerifiedChecked((string) $pending->id);

                    $this->users->upsert([
                        'firebaseUid' => (string) $pending->firebase_uid,
                        'fullName' => (string) $pending->full_name,
                        'email' => (string) $firebaseUser->email,
                        'studentNumber' => $pending->student_number ? (string) $pending->student_number : null,
                        'phoneNumber' => $pending->phone_number ? (string) $pending->phone_number : null,
                    ]);
                }

                $this->pendingRegistrations->markCompleted((string) $pending->id);
            });
        } catch (\Throwable) {
            try {
                DB::transaction(function () use ($merchantPaymentId, $payfastPaymentId): void {
                    $payment = $this->payments->findByMerchantPaymentIdForUpdate($merchantPaymentId);
                    if (! $payment || $payment->status === 'PAID') {
                        return;
                    }

                    $this->payments->markPaid((string) $payment->id, $payfastPaymentId);
                });
            } catch (\Throwable) {
                return;
            }
        }
    }
}
