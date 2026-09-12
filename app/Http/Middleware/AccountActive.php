<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountActive
{
    public function __construct(
        private UserRepository $users
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $firebaseUid = $request->attributes->get('firebase_uid');

        if (!$firebaseUid) {
            return response()->json([
                'message' => 'Authenticated user not found.',
            ], 401);
        }

        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (!$user) {
            return response()->json([
                'message' => 'User account not found.',
            ], 403);
        }

        if ($user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Your account is not active.',
            ], 403);
        }

        $request->attributes->set('current_user', $user);

        return $next($request);
    }
}