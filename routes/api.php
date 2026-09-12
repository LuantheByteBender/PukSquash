<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CoachAvailabilityController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminCourtController;
use Illuminate\Support\Facades\Route;
   
    // Admin
Route::middleware('firebase.auth')->group(function () {
    // User
    Route::get('/me', [UserController::class, 'me']);

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

    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index']);
        Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
        Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
        Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);

        Route::get('/admin/courts', [AdminCourtController::class, 'index']);
        Route::post('/admin/courts', [AdminCourtController::class, 'store']);
        Route::put('/admin/courts/{id}', [AdminCourtController::class, 'update']);
        Route::delete('/admin/courts/{id}', [AdminCourtController::class, 'destroy']);
    });
});
   