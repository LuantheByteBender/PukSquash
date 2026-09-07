<?php

use App\Http\Controllers\CourtController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('firebase.auth')->group(function () {

    Route::get('/me', function (Request $request) {
        return response()->json([
            'authenticated' => true,

            'firebase_uid' =>
                $request->attributes->get('firebase_uid'),

            'email' =>
                $request->attributes->get('firebase_email'),

            'email_verified' =>
                $request->attributes->get(
                    'firebase_email_verified'
                ),
        ]);
    });

    Route::get('/courts', [CourtController::class, 'index']);
});