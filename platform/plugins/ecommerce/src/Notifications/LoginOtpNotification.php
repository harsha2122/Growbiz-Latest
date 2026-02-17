<?php

namespace Botble\Ecommerce\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $otp)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__('Your Login OTP Code'))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('Your one-time password (OTP) for login verification is:'))
            ->line('**' . $this->otp . '**')
            ->line(__('This OTP is valid for 5 minutes. Do not share it with anyone.'))
            ->line(__('If you did not attempt to log in, please ignore this email or change your password immediately.'));
    }
}
