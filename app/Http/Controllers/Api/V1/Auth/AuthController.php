<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendEmailOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function register(
        RegisterRequest $request
    ): JsonResponse {
        $user = $this->authService->register(
            $request->validated()
        );

        return $this->successResponse(
            [
                'user' => new UserResource($user),

                'verification_required' => true,
            ],
            'Registration successful. A verification code has been sent to your email address.',
            201
        );
    }

    public function verifyEmail(
        VerifyEmailOtpRequest $request
    ): JsonResponse {
        $result = $this->authService
            ->verifyEmailOtp(
                $request->string('email')
                    ->toString(),

                $request->string('otp')
                    ->toString(),

                $request->input(
                    'device_name'
                )
            );

        return $this->successResponse(
            [
                'user' => new UserResource(
                    $result['user']
                ),

                'access_token' => $result['token'],

                'token_type' => 'Bearer',
            ],
            'Email verified successfully.'
        );
    }

    public function resendOtp(
        ResendEmailOtpRequest $request
    ): JsonResponse {
        $user = User::query()
            ->where(
                'email',
                strtolower($request->email)
            )
            ->firstOrFail();

        $this->authService
            ->sendEmailVerificationOtp($user);

        return $this->successResponse(
            null,
            'A new verification code has been sent to your email address.'
        );
    }

    public function login(
        LoginRequest $request
    ): JsonResponse {
        $result = $this->authService->login(
            $request->validated()
        );

        return $this->successResponse(
            [
                'user' => new UserResource(
                    $result['user']
                ),

                'access_token' => $result['token'],

                'token_type' => 'Bearer',
            ],
            'Login successful.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Forgot Password
    |--------------------------------------------------------------------------
    */

    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {
        $this->authService
            ->sendPasswordResetOtp(
                $request->string('email')
                    ->toString()
            );

        return $this->successResponse(
            null,
            'If an account exists with this email address, a password reset code has been sent.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $this->authService->resetPassword(
            $request->string('email')
                ->toString(),

            $request->string('otp')
                ->toString(),

            $request->string('password')
                ->toString()
        );

        return $this->successResponse(
            null,
            'Password reset successfully. Please login with your new password.'
        );
    }

    public function me(
        Request $request
    ): JsonResponse {
        return $this->successResponse(
            new UserResource(
                $request->user()
            ),
            'Authenticated user retrieved successfully.'
        );
    }

    public function logout(
        Request $request
    ): JsonResponse {
        $this->authService->logout(
            $request->user()
        );

        return $this->successResponse(
            null,
            'Logout successful.'
        );
    }
}
