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

class UserController extends Controller
{
    public function __construct(
        private PendingRegistrationRepository $pendingRegistrations,
        private RegistrationPaymentRepository $registrationPayments,
        private FirebaseAuthService $firebase,
        private UserRepository $users,
    ) {}

    /**
     * GET /api/me
     *
     * Return the authenticated user's profile from SQL Connect.
     * If the user does not exist yet, upsert a minimal record from
     * the Firebase token claims.
     */
    public function me(Request $request): JsonResponse
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $email       = $request->attributes->get('firebase_email');

        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            $this->attemptRegistrationRecovery((string) $firebaseUid);
            $user = $this->users->findByFirebaseUid((string) $firebaseUid);
        }

        if (! $user) {
            return response()->json([
                'message' => 'Registration payment required.',
                'code' => 'REGISTRATION_PAYMENT_REQUIRED',
                'email' => $email,
            ], 403);
        }

        return response()->json($user->toArray());
    }

    private function attemptRegistrationRecovery(string $firebaseUid): void
    {
        if ($firebaseUid === '') {
            return;
        }

        $pending = $this->pendingRegistrations->findByFirebaseUid($firebaseUid);

        if (! $pending) {
            return;
        }

        $latestPayment = $this->registrationPayments->findLatestForPendingRegistration((string) $pending->id);

        if (! $latestPayment || (string) $latestPayment->status !== 'PAID') {
            return;
        }

        try {
            $firebaseUser = $this->firebase->getUser((string) $pending->firebase_uid);
        } catch (\Throwable) {
            return;
        }

        if ($firebaseUser->emailVerified !== true) {
            return;
        }

        $this->pendingRegistrations->markEmailVerifiedChecked((string) $pending->id);

        $this->users->upsert([
            'firebaseUid' => (string) $pending->firebase_uid,
            'fullName' => (string) $pending->full_name,
            'email' => (string) $pending->email,
            'studentNumber' => $pending->student_number ? (string) $pending->student_number : null,
            'phoneNumber' => $pending->phone_number ? (string) $pending->phone_number : null,
        ]);

        $this->pendingRegistrations->markCompleted((string) $pending->id);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'full_name'      => 'required|string|max:120',
            'student_number' => 'nullable|string|max:30',
            'phone_number'   => 'nullable|string|max:30',
        ]);

        $firebaseUid = $request->attributes->get('firebase_uid');
        $email       = $request->attributes->get('firebase_email');

        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json([
                'message' => 'Registration payment required.',
                'code' => 'REGISTRATION_PAYMENT_REQUIRED',
            ], 403);
        }

        try {
            $this->users->updateProfile(
                $firebaseUid,
                $request->input('full_name'),
                $request->input('student_number'),
                $request->input('phone_number'),
                $email,
            );
        } catch (QueryException $e) {
            $sqlState = $e->errorInfo[0] ?? null;
            $message = $e->getMessage();

            if ($sqlState === '23505' && is_string($message) && str_contains($message, 'users_student_number_key')) {
                return response()->json([
                    'message' => 'Student number is already in use.',
                ], 422);
            }

            throw $e;
        }

        $updated = $this->users->findByFirebaseUid($firebaseUid);

        return response()->json($updated?->toArray());
    }
}
