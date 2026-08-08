<?php

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