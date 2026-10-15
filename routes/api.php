<?php

use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CoachAvailabilityController;
use App\Http\Controllers\Api\CourtController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminCourtController;
use App\Http\Controllers\Api\AdminRegistrationPaymentController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\TeamPracticeController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\PayfastItnController;
use App\Http\Controllers\Api\PayfastDemoController;
use App\Http\Controllers\Api\RegistrationReconcileController;
use App\Http\Controllers\Api\CoachBookingController;
use Illuminate\Support\Facades\Route;

Route::post('/payfast/itn/registration', [PayfastItnController::class, 'registration']);

Route::middleware('firebase.auth')->group(function () {
    Route::get('/me', [UserController::class, 'me']);

    Route::get('/registration/status', [RegistrationController::class, 'status']);
    Route::post('/registration/payment/initiate', [RegistrationController::class, 'initiatePayment']);
    Route::post('/registration/payment/demo/complete', [PayfastDemoController::class, 'completeRegistrationPayment']);
    Route::post('/registration/reconcile', [RegistrationReconcileController::class, 'reconcile']);

    Route::middleware('registered')->group(function () {
        Route::patch('/me', [UserController::class, 'update']);
        Route::post('/me/funds', [UserController::class, 'addFunds']);
        Route::patch('/me/private-coaching-fee', [UserController::class, 'updatePrivateCoachingFee']);

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

        // Coach availability Notice Board
        Route::get('/notice-board/coaches', [CoachAvailabilityController::class, 'noticeBoard']);

        Route::middleware('admin')->group(function () {
            Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
            Route::get('/dashboard/utilisation', [AdminDashboardController::class, 'utilisation']);

            Route::get('/admin/users', [AdminUserController::class, 'index']);
            Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
            Route::put('/admin/users/{id}', [AdminUserController::class, 'update']);
            Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);

            Route::get('/admin/registration-payments', [AdminRegistrationPaymentController::class, 'index']);

            Route::get('/admin/courts', [AdminCourtController::class, 'index']);
            Route::post('/admin/courts', [AdminCourtController::class, 'store']);
            Route::put('/admin/courts/{id}', [AdminCourtController::class, 'update']);
            Route::delete('/admin/courts/{id}', [AdminCourtController::class, 'destroy']);
        });
       
        // Current and upcoming confirmed Coach bookings
        Route::get(
            '/coach/bookings/upcoming',
            [CoachBookingController::class, 'upcoming']
        );
        Route::put('/coach/availability', [CoachAvailabilityController::class, 'publish']);
        Route::get('/coach/availability', [CoachAvailabilityController::class, 'mine']);
        Route::delete('/coach/availability', [CoachAvailabilityController::class, 'destroy']);

        Route::get('/coach/team-practice/availability', [TeamPracticeController::class, 'availability']);
        Route::post('/coach/team-practices', [TeamPracticeController::class, 'store']);
        Route::get('/coach/team-practices', [TeamPracticeController::class, 'mine']);
    });
});