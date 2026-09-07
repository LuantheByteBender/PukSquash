<?php

namespace App\Http\Controllers;

use App\Services\SqlConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function __construct(
        private SqlConnectService $sqlConnect
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $uid = $request->attributes->get('firebase_uid');

        $emailVerified = (bool) $request->attributes->get(
            'firebase_email_verified'
        );

        if (! $emailVerified) {
            return response()->json([
                'message' => 'Email verification is required.',
            ], 403);
        }

        $result = $this->sqlConnect->queryAsUser(
            'ListCourts',
            $uid,
            $emailVerified
        );

        return response()->json([
            'data' => $result['data']['courts'] ?? [],
        ]);
    }
}