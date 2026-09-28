<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordOtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $otp
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(
        object $notifiable
    ): MailMessage {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->greeting(
                'Hello ' . $notifiable->name . ','
            )
            ->line(
                'We received a request to reset your password.'
            )
            ->line(
                'Please use the verification code below:'
            )
            ->line($this->otp)
            ->line(
                'This code will expire in 10 minutes.'
            )
            ->line(
                'If you did not request a password reset, you can safely ignore this email.'
            )
            ->salutation(
                'Regards, ILO Team'
            );
    }
}
