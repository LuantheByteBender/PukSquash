<?php

use App\Http\Controllers\Api\CoachAvailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('firebase.auth')
    ->get('/me', function (Request $request) {

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

//Handles publishing a Coach's selected time slots for a specific date.
Route::middleware('firebase.auth')
    ->put(
        '/coach/availability',
        [CoachAvailabilityController::class, 'publish']
    );