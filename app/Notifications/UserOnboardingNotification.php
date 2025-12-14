<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Password;

class UserOnboardingNotification extends Notification
{
    use Queueable;

    protected string $resetToken;

    public function __construct(string $resetToken)
    {
        $this->resetToken = $resetToken;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $resetUrl = url(route('password.reset', [
            'token' => $this->resetToken,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject('Verify your email and set your password')
            ->greeting('Welcome!')
            ->line('Please verify your email address and set your password to access the portal.')
            ->action('Verify Email', $verificationUrl)
            ->line('After verifying, set your password using the link below (valid once):')
            ->action('Set Password', $resetUrl)
            ->line('If you did not expect this, no further action is needed.');
    }

    protected function verificationUrl(object $notifiable): string
    {
        $expiration = Carbon::now()->addMinutes(config('auth.verification.expire', 60));

        return URL::temporarySignedRoute(
            'verification.verify.guest',
            $expiration,
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
