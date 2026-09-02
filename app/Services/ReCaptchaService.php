<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ReCaptchaService
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function isEnabled(): bool
    {
        return !empty(config('services.recaptcha.site_key'))
            && !empty(config('services.recaptcha.secret_key'));
    }

    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $response = Http::asForm()->withoutVerifying()->post(self::VERIFY_URL, [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $token,
            'remoteip' => $remoteIp,
        ]);

        if ($response->failed()) {
            return false;
        }

        return (bool) ($response->json('success') ?? false);
    }
}
