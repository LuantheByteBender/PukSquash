<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\PendingRegistrationRepository;
use App\Repositories\RegistrationPaymentRepository;
use App\Repositories\UserRepository;
use App\Services\FirebaseAuthService;
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
        $relaxItn = (bool) config('payfast.relax_itn');

        if (! ($sandbox && $relaxItn)) {
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
            ], 404);
        }

        $latest = $this->payments->findLatestForPendingRegistration((string) $pending->id);
        if (! $latest) {
            return response()->json([
                'registered' => false,
                'message' => 'No payment found.',
            ], 409);
        }

        $merchantPaymentId = (string) ($latest->merchant_payment_id ?? '');
        if ($merchantPaymentId === '') {
            return response()->json([
                'registered' => false,
                'message' => 'Invalid payment reference.',
            ], 409);
        }

        $payfastPaymentId = 'DEMO-' . Str::uuid()->toString();

        $this->attemptCompletePayment($merchantPaymentId, $payfastPaymentId);

        $user = $this->users->findByFirebaseUid($firebaseUid);

        return response()->json([
            'registered' => (bool) $user,
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
                        'email' => (string) $pending->email,
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
