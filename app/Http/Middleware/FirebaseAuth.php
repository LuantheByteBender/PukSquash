<?php

namespace App\Http\Middleware;

use App\Services\FirebaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Exception\Auth\UserNotFound;
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

        // 1. Verify the token. Only a token that Firebase itself rejects
        //    (expired, malformed, wrong project) is a 401. Anything else is
        //    our infrastructure failing and must not look like an expired session.
        try {
            $verifiedToken = $this->firebaseAuth->verifyToken($token);
            $uid = $verifiedToken->claims()->get('sub');
        } catch (FailedToVerifyToken $exception) {
            return response()->json([
                'message' => 'Invalid authentication token.',
                'error' => $exception->getMessage(),
            ], 401);
        } catch (\Throwable $exception) {
            return $this->temporaryFailure('verifyToken', $exception);
        }

        // 2. Look the user up. A missing user is a 401; a Firebase outage is not.
        try {
            $firebaseUser = $this->firebaseAuth->getUser($uid);
        } catch (UserNotFound $exception) {
            return response()->json([
                'message' => 'Invalid authentication token.',
                'error' => $exception->getMessage(),
            ], 401);
        } catch (\Throwable $exception) {
            return $this->temporaryFailure('getUser', $exception);
        }

        $request->attributes->set('firebase_uid', $uid);
        $request->attributes->set('firebase_email', $firebaseUser->email);
        $request->attributes->set('firebase_email_verified', $firebaseUser->emailVerified);

        $isMeEndpoint = $request->is('api/me') && $request->method() === 'GET';

        if ($firebaseUser->emailVerified === false && ! $isMeEndpoint) {
            return response()->json([
                'message' => 'Email address not verified.',
            ], 403);
        }

        return $next($request);
    }

    /**
     * Firebase could not be reached or errored for a reason that is not the
     * user's token. Log the real cause and tell the client to retry rather than
     * to sign in again.
     */
    private function temporaryFailure(string $stage, \Throwable $exception): Response
    {
        Log::warning('Firebase auth check failed at ' . $stage, [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
        ]);

        return response()->json([
            'message' => 'We could not verify your session right now. Please try again in a moment.',
        ], 503);
    }
}
