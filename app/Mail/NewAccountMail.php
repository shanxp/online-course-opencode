<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;

class NewAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $plainPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.mail_account_subject'),
        );
    }

    public function content(): Content
    {
        $locale = App::getLocale();

        return new Content(
            view: $locale === 'de' ? 'mail.new-account-de' : 'mail.new-account-en',
            with: [
                'name' => $this->user->name,
                'username' => $this->user->username,
                'password' => $this->plainPassword,
                'appName' => config('app.name'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
