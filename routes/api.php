<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\DisputeType\DisputeTypeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {

        Route::post(
            '/register',
            [AuthController::class, 'register']
        )->middleware('throttle:10,1');

        Route::post(
            '/verify-email',
            [AuthController::class, 'verifyEmail']
        )->middleware('throttle:10,1');

        Route::post(
            '/resend-otp',
            [AuthController::class, 'resendOtp']
        )->middleware('throttle:3,1');

        Route::post(
            '/login',
            [AuthController::class, 'login']
        )->middleware('throttle:10,1');

        /*
        |--------------------------------------------------------------------------
        | Password Reset
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/forgot-password',
            [AuthController::class, 'forgotPassword']
        )->middleware('throttle:3,1');

        Route::post(
            '/reset-password',
            [AuthController::class, 'resetPassword']
        )->middleware('throttle:5,1');

        /*
        |--------------------------------------------------------------------------
        | Authenticated Auth Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware(
            'auth:sanctum'
        )->group(function () {

            Route::get(
                '/me',
                [AuthController::class, 'me']
            );

            Route::post(
                '/logout',
                [AuthController::class, 'logout']
            );
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Application Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware(
        'auth:sanctum'
    )->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dispute Types
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dispute-types',
            [DisputeTypeController::class, 'index']
        );

        Route::post(
            '/dispute-types',
            [DisputeTypeController::class, 'store']
        );

        Route::get(
            '/dispute-types/{disputeType}',
            [DisputeTypeController::class, 'show']
        );

        Route::put(
            '/dispute-types/{disputeType}',
            [DisputeTypeController::class, 'update']
        );

        Route::patch(
            '/dispute-types/{disputeType}',
            [DisputeTypeController::class, 'update']
        );

        Route::delete(
            '/dispute-types/{disputeType}',
            [DisputeTypeController::class, 'destroy']
        );
    });
});
