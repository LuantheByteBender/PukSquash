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
            $this->users->upsert([
                'firebaseUid' => $firebaseUid,
                'fullName'    => explode('@', $email)[0],
                'email'       => $email,
            ]);

            $user = $this->users->findByFirebaseUid($firebaseUid);
        }

        return response()->json($user?->toArray());
    }
}
