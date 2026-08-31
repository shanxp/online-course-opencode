<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $newPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.mail_password_reset_subject'),
        );
    }

    public function content(): Content
    {
        $locale = App::getLocale();

        return new Content(
            view: $locale === 'de' ? 'mail.password-reset-de' : 'mail.password-reset-en',
            with: [
                'name' => $this->user->name,
                'username' => $this->user->username,
                'password' => $this->newPassword,
                'appName' => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
