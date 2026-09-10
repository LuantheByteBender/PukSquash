<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;

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
    
Route::middleware('firebase.auth')
    ->get('/student/profile', [StudentController::class, 'profile']);