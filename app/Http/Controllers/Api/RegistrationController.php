<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\PendingRegistrationRepository;
use App\Repositories\RegistrationPaymentRepository;
use App\Repositories\UserRepository;
use App\Services\PayfastService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function __construct(
        private PendingRegistrationRepository $pendingRegistrations,
        private RegistrationPaymentRepository $registrationPayments,
        private UserRepository $users,
        private PayfastService $payfast,
    ) {
    }

    public function status(Request $request): JsonResponse
    {
        $firebaseUid = (string) $request->attributes->get('firebase_uid');
        $email = trim((string) $request->attributes->get('firebase_email'));
        $emailVerified = (bool) $request->attributes->get('firebase_email_verified');

        if (! $emailVerified) {
            return response()->json([
                'message' => 'Email address not verified.',
                'code' => 'EMAIL_NOT_VERIFIED',
            ], 403);
        }

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
                'pending_registration' => null,
                'latest_payment' => null,
                'email' => $email,
            ]);
        }

        $latestPayment = $this->registrationPayments->findLatestForPendingRegistration((string) $pending->id);

        return response()->json([
            'registered' => false,
            'pending_registration' => $pending,
            'latest_payment' => $latestPayment,
        ]);
    }

    public function initiatePayment(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:120',
            'student_number' => 'nullable|string|max:30',
            'phone_number' => 'nullable|string|max:30',
        ]);

        $firebaseUid = (string) $request->attributes->get('firebase_uid');
        $email = trim((string) $request->attributes->get('firebase_email'));

        if ($this->users->findByFirebaseUid($firebaseUid)) {
            return response()->json([
                'message' => 'Already registered.',
                'code' => 'ALREADY_REGISTERED',
            ], 409);
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $notifyUrl = trim($baseUrl . '/api/payfast/itn/registration');
        $returnUrl = trim($baseUrl . '/payment/return');
        $cancelUrl = trim($baseUrl . '/payment/cancel');

        $merchantId = trim((string) config('payfast.merchant_id'));
        $merchantKey = trim((string) config('payfast.merchant_key'));

        $amount = (string) config('payfast.registration_fee');
        $amountCents = $this->payfast->amountStringToCents($amount);

        $pending = $this->pendingRegistrations->createOrGet(
            $firebaseUid,
            $email,
            (string) $request->input('full_name'),
            $request->input('student_number'),
            $request->input('phone_number'),
        );

        $this->pendingRegistrations->markEmailVerifiedChecked((string) $pending->id);

        $merchantPaymentId = (string) Str::uuid();

        $payment = $this->registrationPayments->createPending(
            (string) $pending->id,
            $merchantPaymentId,
            $amountCents,
            'ZAR',
            'PAYFAST',
        );

        $fields = [
            'merchant_id' => $merchantId,
            'merchant_key' => $merchantKey,
            'return_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'notify_url' => $notifyUrl,
            'email_address' => $email,
            'm_payment_id' => $merchantPaymentId,
            'amount' => number_format($amountCents / 100, 2, '.', ''),
            'item_name' => trim('PukSquash Registration'),
            'custom_str1' => (string) $pending->id,
            'custom_str2' => $firebaseUid,
        ];

        $fields['signature'] = $this->payfast->signature($fields);

        return response()->json([
            'payfast' => [
                'process_url' => $this->payfast->processUrl(),
                'fields' => $fields,
            ],
            'pending_registration_id' => (string) $pending->id,
            'registration_payment_id' => (string) $payment->id,
            'merchant_payment_id' => $merchantPaymentId,
        ]);
    }
}
