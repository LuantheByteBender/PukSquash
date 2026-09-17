<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CoachAvailabilityController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CoachBookingController;

Route::middleware('firebase.auth')->group(function () {
    // User
    Route::get('/me', [UserController::class, 'me']);
    Route::patch('/me', [UserController::class, 'update']);

    // Courts
    Route::get('/courts', [CourtController::class, 'index']);
    Route::get('/courts/{id}', [CourtController::class, 'show']);

    // Time slots
    Route::get('/time-slots', [TimeSlotController::class, 'index']);

    // Availability grid
    Route::get('/availability', [AvailabilityController::class, 'index']);

    // Bookings
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/mine', [BookingController::class, 'mine']);
    Route::post('/bookings/{id}/join', [BookingController::class, 'join']);
    Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Coach availability
    Route::get('/coaches/available', [CoachAvailabilityController::class, 'index']);

    // Current and upcoming confirmed Coach bookings
    Route::get(
        '/coach/bookings/upcoming',
        [CoachBookingController::class, 'upcoming']
    );
    Route::put('/coach/availability', [CoachAvailabilityController::class, 'publish']);
    Route::get('/coach/availability', [CoachAvailabilityController::class, 'mine']);
    Route::delete('/coach/availability', [CoachAvailabilityController::class, 'destroy']);
});