<?php

namespace App\Models;

use App\Support\EmailNotificationCatalog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_enabled',
        'provider',
        'from_name',
        'from_email',
        'reply_to_email',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'ses_region',
        'ses_key',
        'ses_secret',
        'notification_toggles',
    ];

    protected function casts(): array
    {
        return [
            'mail_enabled' => 'boolean',
            'smtp_port' => 'integer',
            'smtp_password' => 'encrypted',
            'ses_key' => 'encrypted',
            'ses_secret' => 'encrypted',
            'notification_toggles' => 'array',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'mail_enabled' => true,
            'provider' => 'ses_smtp',
            'from_name' => config('mail.from.name'),
            'from_email' => config('mail.from.address'),
            'ses_region' => 'eu-west-1',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'notification_toggles' => EmailNotificationCatalog::defaultEnabledKeys(),
        ]);
    }

    public function notificationIsEnabled(string $key): bool
    {
        $enabled = $this->notification_toggles;

        if ($enabled === null) {
            return true;
        }

        if (array_is_list($enabled)) {
            return in_array($key, $enabled, true);
        }

        return (bool) ($enabled[$key] ?? false);
    }

    public function applyMailConfig(): void
    {
        config([
            'mail.from.address' => $this->from_email ?: config('mail.from.address'),
            'mail.from.name' => $this->from_name ?: config('mail.from.name'),
        ]);

        if ($this->provider === 'system') {
            return;
        }

        $region = $this->ses_region ?: 'eu-west-1';
        $host = sprintf('email-smtp.%s.amazonaws.com', $region);
        $port = $this->smtp_port ?: ($this->smtp_encryption === 'ssl' ? 465 : 587);
        $username = $this->smtp_username ?: $this->ses_key;
        $password = $this->smtp_password ?: $this->ses_secret;

        if (blank($username) || blank($password)) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.scheme' => $this->smtp_encryption === 'ssl' ? 'smtps' : null,
        ]);
    }
}
