<?php

namespace App\Http\Middleware;

use App\Services\FirebaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuth
{
    public function __construct(
        private FirebaseAuthService $firebaseAuth
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Authentication token missing.',
            ], 401);
        }

        try {
            $verifiedToken =
                $this->firebaseAuth->verifyToken($token);

            $uid = $verifiedToken
                ->claims()
                ->get('sub');

            $firebaseUser =
                $this->firebaseAuth->getUser($uid);

            $request->attributes->set(
                'firebase_uid',
                $uid
            );

            $request->attributes->set(
                'firebase_email',
                $firebaseUser->email
            );

            $request->attributes->set(
                'firebase_email_verified',
                $firebaseUser->emailVerified
            );

            return $next($request);

        } catch (\Throwable $exception) {
            return response()->json([
                'message' => 'Invalid authentication token.',
                'error' => $exception->getMessage(),
            ], 401);
        }
    }
}