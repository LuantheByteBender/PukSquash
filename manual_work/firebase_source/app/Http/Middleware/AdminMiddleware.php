<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
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

        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json([
                'message' => 'User profile not found.',
            ], 403);
        }

        if ($user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'User is not authorised to do that.',
            ], 403);
        }

        if ($user->role !== 'ADMIN') {
            return response()->json([
                'message' => 'User is not authorised to do that.',
            ], 403);
        }

        return $next($request);
    }
}
