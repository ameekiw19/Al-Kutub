<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmailNotification extends VerifyEmail
{
    /**
     * Build the mail representation for verification email.
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Email Akun Al-Kutub')
            ->line('Terima kasih sudah mendaftar di Al-Kutub.')
            ->line('Klik tombol di bawah untuk memverifikasi email Anda.')
            ->action('Verifikasi Email', $verificationUrl)
            ->line('Link verifikasi ini berlaku selama 60 menit.');
    }

    /**
     * Generate custom signed verification URL (public route, no login required).
     */
    protected function verificationUrl($notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify.public',
            Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}
