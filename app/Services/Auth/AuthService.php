<?php

namespace App\Services\Auth;

use App\Enums\OtpPurpose;
use App\Enums\TokenAbility;
use App\Models\EmailVerificationOtp;
use App\Models\User;
use App\Notifications\ResetPasswordOtpNotification;
use App\Notifications\VerifyEmailOtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    private const OTP_EXPIRATION_MINUTES = 10;

    private const MAX_OTP_ATTEMPTS = 5;

    /*
    |--------------------------------------------------------------------------
    | Register
    |--------------------------------------------------------------------------
    */

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => $data['password'],
            ]);

            $this->sendEmailVerificationOtp($user);

            return $user;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Email Verification OTP
    |--------------------------------------------------------------------------
    */

    public function sendEmailVerificationOtp(
        User $user
    ): void {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [
                    'Email address is already verified.',
                ],
            ]);
        }

        $otp = $this->generateOtp();

        $this->storeOtp(
            user: $user,
            purpose: OtpPurpose::EMAIL_VERIFICATION,
            otp: $otp
        );

        $user->notify(
            new VerifyEmailOtpNotification($otp)
        );
    }

    public function verifyEmailOtp(
        string $email,
        string $otp,
        ?string $deviceName = null
    ): array {
        return DB::transaction(function () use (
            $email,
            $otp,
            $deviceName
        ) {
            $user = User::query()
                ->where(
                    'email',
                    strtolower($email)
                )
                ->firstOrFail();

            if ($user->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    'email' => [
                        'Email address is already verified.',
                    ],
                ]);
            }

            $verification = $this->getOtpForUpdate(
                user: $user,
                purpose: OtpPurpose::EMAIL_VERIFICATION
            );

            $this->validateOtp(
                verification: $verification,
                otp: $otp
            );

            $user->markEmailAsVerified();

            $verification->update([
                'used_at' => now(),
            ]);

            $token = $user->createToken(
                $deviceName ?: 'api-token',
                [
                    TokenAbility::ACCESS_API->value,
                ]
            )->plainTextToken;

            return [
                'user' => $user->fresh(),
                'token' => $token,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Login
    |--------------------------------------------------------------------------
    */

    public function login(array $data): array
    {
        $user = User::query()
            ->where(
                'email',
                strtolower($data['email'])
            )
            ->first();

        if (
            ! $user ||
            ! Hash::check(
                $data['password'],
                $user->password
            )
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Invalid email address or password.',
                ],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => [
                    'Please verify your email address before logging in.',
                ],
            ]);
        }

        $token = $user->createToken(
            $data['device_name'] ?? 'api-token',
            [
                TokenAbility::ACCESS_API->value,
            ]
        )->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Forgot Password
    |--------------------------------------------------------------------------
    */

    public function sendPasswordResetOtp(
        string $email
    ): void {
        $user = User::query()
            ->where(
                'email',
                strtolower($email)
            )
            ->first();

        /*
         * Important:
         *
         * Do not throw "user not found" here.
         * This prevents email enumeration.
         */
        if (! $user) {
            return;
        }

        $otp = $this->generateOtp();

        $this->storeOtp(
            user: $user,
            purpose: OtpPurpose::PASSWORD_RESET,
            otp: $otp
        );

        $user->notify(
            new ResetPasswordOtpNotification($otp)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Password
    |--------------------------------------------------------------------------
    */

    public function resetPassword(
        string $email,
        string $otp,
        string $password
    ): void {
        DB::transaction(function () use (
            $email,
            $otp,
            $password
        ) {
            $user = User::query()
                ->where(
                    'email',
                    strtolower($email)
                )
                ->lockForUpdate()
                ->first();

            if (! $user) {
                throw ValidationException::withMessages([
                    'otp' => [
                        'Invalid or expired verification code.',
                    ],
                ]);
            }

            $verification = $this->getOtpForUpdate(
                user: $user,
                purpose: OtpPurpose::PASSWORD_RESET
            );

            $this->validateOtp(
                verification: $verification,
                otp: $otp
            );

            /*
             * If User model has:
             *
             * 'password' => 'hashed'
             *
             * Laravel will automatically hash this.
             */
            $user->password = $password;

            $user->save();

            /*
             * OTP can only be used once.
             */
            $verification->update([
                'used_at' => now(),
            ]);

            /*
             * Security:
             * Logout user from all existing devices/tokens.
             */
            $user->tokens()->delete();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | OTP Helpers
    |--------------------------------------------------------------------------
    */

    private function generateOtp(): string
    {
        return (string) random_int(
            100000,
            999999
        );
    }

    private function storeOtp(
        User $user,
        OtpPurpose $purpose,
        string $otp
    ): void {
        EmailVerificationOtp::updateOrCreate(
            [
                'user_id' => $user->id,

                'purpose' => $purpose->value,
            ],
            [
                'otp_hash' => Hash::make($otp),

                'attempts' => 0,

                'expires_at' => now()->addMinutes(
                    self::OTP_EXPIRATION_MINUTES
                ),

                'used_at' => null,
            ]
        );
    }

    private function getOtpForUpdate(
        User $user,
        OtpPurpose $purpose
    ): EmailVerificationOtp {
        $verification = EmailVerificationOtp::query()
            ->where(
                'user_id',
                $user->id
            )
            ->where(
                'purpose',
                $purpose->value
            )
            ->lockForUpdate()
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'otp' => [
                    'Invalid or expired verification code.',
                ],
            ]);
        }

        return $verification;
    }

    private function validateOtp(
        EmailVerificationOtp $verification,
        string $otp
    ): void {
        if ($verification->used_at) {
            throw ValidationException::withMessages([
                'otp' => [
                    'This verification code has already been used.',
                ],
            ]);
        }

        if (
            now()->greaterThan(
                $verification->expires_at
            )
        ) {
            throw ValidationException::withMessages([
                'otp' => [
                    'Verification code has expired. Please request a new code.',
                ],
            ]);
        }

        if (
            $verification->attempts
            >= self::MAX_OTP_ATTEMPTS
        ) {
            throw ValidationException::withMessages([
                'otp' => [
                    'Maximum verification attempts exceeded. Please request a new code.',
                ],
            ]);
        }

        if (
            ! Hash::check(
                $otp,
                $verification->otp_hash
            )
        ) {
            $verification->increment(
                'attempts'
            );

            throw ValidationException::withMessages([
                'otp' => [
                    'Invalid verification code.',
                ],
            ]);
        }
    }
}
