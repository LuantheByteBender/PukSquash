<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
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
            $studentNumber = $email ? explode('@', $email)[0] : null;

            $this->users->upsert([
                'firebaseUid' => $firebaseUid,
                'fullName'    => explode('@', $email)[0],
                'email'       => $email,
                'studentNumber' => $studentNumber,
            ]);

            $user = $this->users->findByFirebaseUid($firebaseUid);
        }

        return response()->json($user?->toArray());
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'full_name'      => 'required|string|max:120',
            'student_number' => 'nullable|string|max:30',
        ]);

        $firebaseUid = $request->attributes->get('firebase_uid');
        $email       = $request->attributes->get('firebase_email');

        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            $studentNumber = $email ? explode('@', $email)[0] : null;

            $this->users->upsert([
                'firebaseUid' => $firebaseUid,
                'fullName'    => explode('@', $email)[0],
                'email'       => $email,
                'studentNumber' => $studentNumber,
            ]);
        }

        $this->users->updateProfile(
            $firebaseUid,
            $request->input('full_name'),
            $request->input('student_number'),
        );

        $updated = $this->users->findByFirebaseUid($firebaseUid);

        return response()->json($updated?->toArray());
    }
}
