<?php

namespace App\Services;

use App\Models\EmailVerificationCode;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BuyerEmailVerificationService
{
    public const EXPIRES_MINUTES = 15;

    public function __construct(private readonly EmailNotificationService $emails) {}

    public function issue(string $email): string
    {
        return $this->issueCode($email, 'verification');
    }

    public function issuePasswordReset(string $email): string
    {
        return $this->issueCode($email, 'password_reset');
    }

    private function issueCode(string $email, string $purpose): string
    {
        $email = Str::lower($email);
        $code = (string) random_int(100000, 999999);

        EmailVerificationCode::query()->updateOrCreate([
            'email' => $email,
        ], [
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
            'verified_at' => null,
            'last_sent_at' => now(),
            'attempts' => 0,
        ]);

        if ($purpose === 'password_reset') {
            $this->emails->sendBuyerPasswordReset($email, $code, self::EXPIRES_MINUTES);
        } else {
            $this->emails->sendBuyerEmailVerification($email, $code, self::EXPIRES_MINUTES);
        }

        return $code;
    }

    public function verify(string $email, string $code): bool
    {
        $record = EmailVerificationCode::query()
            ->where('email', Str::lower($email))
            ->first();

        if (! $record || $record->expires_at->isPast()) {
            return false;
        }

        if (! Hash::check($code, $record->code_hash)) {
            $record->increment('attempts');

            return false;
        }

        $record->forceFill([
            'verified_at' => now(),
        ])->save();

        return true;
    }
}
