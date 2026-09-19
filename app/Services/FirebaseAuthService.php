<?php

namespace App\Services;

use Kreait\Firebase\Auth;
use Kreait\Firebase\Factory;

class FirebaseAuthService
{
    private Auth $auth;

    public function __construct()
    {
        $credentialsPath = base_path(
            env('FIREBASE_CREDENTIALS')
        );

        $this->auth = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->createAuth();
    }

    public function verifyToken(string $idToken)
    {
        return $this->auth->verifyIdToken($idToken);
    }

    public function getUser(string $uid)
    {
        return $this->auth->getUser($uid);
    }

    public function deleteUser(string $uid): void
    {
        $this->auth->deleteUser($uid);
    }
}