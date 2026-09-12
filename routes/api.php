<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CoachAvailabilityController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('firebase.auth')->group(function () {
    // User
    Route::get('/me', [UserController::class, 'me']);
    Route::patch('/me', [UserController::class, 'update'])
    ->middleware('account.active');

    // Courts
    Route::get('/courts', [CourtController::class, 'index']);
    Route::get('/courts/{id}', [CourtController::class, 'show']);

    // Time slots
    Route::get('/time-slots', [TimeSlotController::class, 'index']);

    // Availability grid
    Route::get('/availability', [AvailabilityController::class, 'index']);

    // Bookings - teammate's routes
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/mine', [BookingController::class, 'mine']);
    Route::post('/bookings/{id}/join', [BookingController::class, 'join']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Coach availability
    Route::get('/coaches/available', [CoachAvailabilityController::class, 'index']);
});