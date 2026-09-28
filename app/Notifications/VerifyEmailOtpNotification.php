<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailOtpNotification extends Notification
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify Your Email Address')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Please use the verification code below to verify your email address.')
            ->line($this->otp)
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request this code, you can ignore this email.')
            ->salutation('Regards, ILO Team');
    }
}
