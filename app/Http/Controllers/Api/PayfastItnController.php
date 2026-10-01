<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\PendingRegistrationRepository;
use App\Repositories\RegistrationPaymentRepository;
use App\Repositories\UserRepository;
use App\Services\FirebaseAuthService;
use App\Services\PayfastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class PayfastItnController extends Controller
{
    public function __construct(
        private PayfastService $payfast,
        private RegistrationPaymentRepository $payments,
        private PendingRegistrationRepository $pendingRegistrations,
        private UserRepository $users,
        private FirebaseAuthService $firebase,
    ) {
    }

    public function registration(Request $request): Response
    {
        $data = $request->all();

        $sandbox = (bool) config('payfast.sandbox');
        $relaxItn = (bool) config('payfast.relax_itn');

        if (! $relaxItn) {
            if (! $this->payfast->isValidSourceIp((string) $request->ip())) {
                return response('Invalid source', 400);
            }

            if (! $this->payfast->signatureMatches($data)) {
                return response('Invalid signature', 400);
            }
        }

        if ((string) ($data['merchant_id'] ?? '') !== (string) config('payfast.merchant_id')) {
            return response('Invalid merchant', 400);
        }

        if (! ($sandbox && $relaxItn)) {
            if (! $this->payfast->validateItn($data)) {
                return response('ITN validation failed', 400);
            }
        }

        $merchantPaymentId = (string) ($data['m_payment_id'] ?? '');
        if ($merchantPaymentId === '') {
            return response('Missing m_payment_id', 400);
        }

        $payfastPaymentId = (string) ($data['pf_payment_id'] ?? '');
        $paymentStatus = strtoupper((string) ($data['payment_status'] ?? ''));
        $incomingAmountCents = $this->payfast->amountStringToCents((string) ($data['amount_gross'] ?? '0'));

        $found = true;
        $amountValid = true;
        $completeRequested = false;

        DB::transaction(function () use (&$found, &$amountValid, &$completeRequested, $merchantPaymentId, $payfastPaymentId, $paymentStatus, $incomingAmountCents): void {
            $payment = $this->payments->findByMerchantPaymentIdForUpdate($merchantPaymentId);
            if (! $payment) {
                $found = false;

                return;
            }

            if ((int) $payment->amount_cents !== $incomingAmountCents) {
                $amountValid = false;

                return;
            }

            if ($payment->status === 'PAID') {
                $completeRequested = true;

                return;
            }

            if ($paymentStatus !== 'COMPLETE') {
                if ($paymentStatus === 'CANCELLED') {
                    $this->payments->markCancelled((string) $payment->id);

                    return;
                }

                $this->payments->markFailed((string) $payment->id, $payfastPaymentId);

                return;
            }

            $completeRequested = true;
        });

        if (! $found) {
            return response('Unknown payment', 404);
        }

        if (! $amountValid) {
            return response('Invalid amount', 400);
        }

        if ($completeRequested) {
            $this->attemptCompletePayment($merchantPaymentId, $payfastPaymentId);
        }

        return response('OK', 200);
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

                // Atomic completion: payment PAID + user creation + pending COMPLETED.
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
            // Best-effort preservation: if completion failed due to DB issues,
            // still try to mark payment as PAID so it can be reconciled later.
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
