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

class RegistrationReconcileController extends Controller
{
    public function __construct(
        private PendingRegistrationRepository $pendingRegistrations,
        private RegistrationPaymentRepository $payments,
        private UserRepository $users,
        private FirebaseAuthService $firebase,
    ) {
    }

    /**
     * Attempts to complete registration after a verified payment exists.
     * This is used as a recovery path when ITN marked payment PAID but user creation failed due to temporary Firebase/DB issues.
     */
    public function reconcile(Request $request): JsonResponse
    {
        $firebaseUid = (string) $request->attributes->get('firebase_uid');
        $firebaseEmail = trim((string) $request->attributes->get('firebase_email'));
        $firebaseEmailVerified = (bool) $request->attributes->get('firebase_email_verified');

        if ($this->users->findByFirebaseUid($firebaseUid)) {
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

        $paid = $this->payments->findPaidForPendingRegistration((string) $pending->id);
        if (! $paid) {
            return response()->json([
                'registered' => false,
                'message' => 'No verified payment found.',
            ], 409);
        }

        $studentNumber = $pending->student_number ? trim((string) $pending->student_number) : '';
        if ($studentNumber !== '') {
            $existing = DB::table('users')
                ->where('student_number', $studentNumber)
                ->first();

            if ($existing && (string) ($existing->firebase_uid ?? '') !== $firebaseUid) {
                return response()->json([
                    'registered' => false,
                    'message' => 'Student number is already registered.',
                    'code' => 'STUDENT_NUMBER_ALREADY_REGISTERED',
                ], 409);
            }
        }

        DB::transaction(function () use ($pending, $firebaseEmail, $firebaseEmailVerified): void {
            $pendingLocked = DB::table('pending_registrations')
                ->where('id', (string) $pending->id)
                ->lockForUpdate()
                ->first();

            if (! $pendingLocked) {
                return;
            }

            if ($this->users->findByFirebaseUid((string) $pendingLocked->firebase_uid)) {
                $this->pendingRegistrations->markCompleted((string) $pendingLocked->id);

                return;
            }

            if ($firebaseEmailVerified !== true) {
                return;
            }

            $this->pendingRegistrations->markEmailVerifiedChecked((string) $pendingLocked->id);

            $this->users->upsert([
                'firebaseUid' => (string) $pendingLocked->firebase_uid,
                'fullName' => (string) $pendingLocked->full_name,
                'email' => $firebaseEmail !== '' ? $firebaseEmail : (string) ($pendingLocked->email ?? ''),
                'studentNumber' => $pendingLocked->student_number ? (string) $pendingLocked->student_number : null,
                'phoneNumber' => $pendingLocked->phone_number ? (string) $pendingLocked->phone_number : null,
            ]);

            $this->pendingRegistrations->markCompleted((string) $pendingLocked->id);
        });

        $user = $this->users->findByFirebaseUid($firebaseUid);

        return response()->json([
            'registered' => (bool) $user,
            'user' => $user?->toArray(),
        ]);
    }
}
