<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegisteredUser
{
    public function __construct(
        private UserRepository $users,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $firebaseUid = $request->attributes->get('firebase_uid');

        if (! $firebaseUid) {
            return response()->json([
                'message' => 'Authentication required.',
            ], 401);
        }

        $user = $this->users->findByFirebaseUid((string) $firebaseUid);

        if (! $user) {
            return response()->json([
                'message' => 'Registration payment required.',
                'code' => 'REGISTRATION_PAYMENT_REQUIRED',
            ], 403);
        }

        return $next($request);
    }
}
