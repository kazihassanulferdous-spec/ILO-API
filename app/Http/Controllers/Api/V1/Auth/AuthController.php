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
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: '/api/v1/auth/register',
        summary: 'Register user',
        description: 'Register a new user and send a 6-digit email verification OTP.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'email',
                    'password',
                    'password_confirmation',
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        example: 'Ahmed Tajim Islam'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'tajim@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'Password123'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: 'Password123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Registration successful'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
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

    /*
    |--------------------------------------------------------------------------
    | Verify Email
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: '/api/v1/auth/verify-email',
        summary: 'Verify email address',
        description: 'Verify user email using the 6-digit OTP.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'email',
                    'otp',
                ],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'tajim@example.com'
                    ),
                    new OA\Property(
                        property: 'otp',
                        type: 'string',
                        example: '123456'
                    ),
                    new OA\Property(
                        property: 'device_name',
                        type: 'string',
                        example: 'Chrome'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Email verified successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid or expired OTP'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
    public function verifyEmail(
        VerifyEmailOtpRequest $request
    ): JsonResponse {
        $result = $this->authService
            ->verifyEmailOtp(
                $request->string('email')->toString(),
                $request->string('otp')->toString(),
                $request->input('device_name')
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

    /*
    |--------------------------------------------------------------------------
    | Resend Verification OTP
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: '/api/v1/auth/resend-otp',
        summary: 'Resend email verification OTP',
        description: 'Send a new email verification OTP.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'email',
                ],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'tajim@example.com'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'OTP sent successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
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

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: '/api/v1/auth/login',
        summary: 'User login',
        description: 'Authenticate user using email and password.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'email',
                    'password',
                ],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'tajim@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'Password123'
                    ),
                    new OA\Property(
                        property: 'device_name',
                        type: 'string',
                        example: 'Chrome'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful'
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid credentials or validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/auth/forgot-password',
        summary: 'Forgot password',
        description: 'Send a password reset OTP to the supplied email address.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'email',
                ],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'tajim@example.com'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset request processed'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
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

    #[OA\Post(
        path: '/api/v1/auth/reset-password',
        summary: 'Reset password',
        description: 'Reset user password using the password reset OTP.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'email',
                    'otp',
                    'password',
                    'password_confirmation',
                ],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'tajim@example.com'
                    ),
                    new OA\Property(
                        property: 'otp',
                        type: 'string',
                        example: '123456'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'NewPassword123'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: 'NewPassword123'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Invalid OTP or validation error'
            ),
            new OA\Response(
                response: 429,
                description: 'Too many requests'
            ),
        ]
    )]
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

    /*
    |--------------------------------------------------------------------------
    | Authenticated User
    |--------------------------------------------------------------------------
    */

    #[OA\Get(
        path: '/api/v1/auth/me',
        summary: 'Get authenticated user',
        description: 'Return the currently authenticated user.',
        tags: ['Authentication'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated user retrieved successfully'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
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

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    #[OA\Post(
        path: '/api/v1/auth/logout',
        summary: 'Logout user',
        description: 'Delete the current Sanctum access token.',
        tags: ['Authentication'],
        security: [
            [
                'bearerAuth' => [],
            ],
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logout successful'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
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
