<?php

namespace Tests\Feature;

use App\Mail\TemplatedEmail;
use App\Models\EmailDeliveryLog;
use App\Models\EmailSetting;
use App\Models\User;
use App\Services\BuyerEmailVerificationService;
use App\Services\EmailNotificationService;
use App\Support\EmailNotificationCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_email_verification_sends_mail_and_records_delivery_log(): void
    {
        Mail::fake();

        EmailSetting::query()->create([
            'mail_enabled' => true,
            'provider' => 'system',
            'from_name' => 'Estate Agents Main',
            'from_email' => 'hello@example.test',
            'notification_toggles' => EmailNotificationCatalog::defaultEnabledKeys(),
        ]);

        $code = app(BuyerEmailVerificationService::class)->issue('buyer@example.test');

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertTrue(app(BuyerEmailVerificationService::class)->verify('buyer@example.test', $code));

        Mail::assertSent(TemplatedEmail::class, function (TemplatedEmail $mail) use ($code): bool {
            return str_contains($mail->subjectLine, 'verification code')
                && str_contains($mail->textBody, $code);
        });

        $this->assertDatabaseHas(EmailDeliveryLog::class, [
            'template_key' => 'buyer_email_verification',
            'recipient_email' => 'buyer@example.test',
            'status' => 'sent',
        ]);
    }

    public function test_buyer_email_verification_route_uses_toast_flash(): void
    {
        Mail::fake();

        EmailSetting::query()->create([
            'mail_enabled' => true,
            'provider' => 'system',
            'from_name' => 'Estate Agents Main',
            'from_email' => 'hello@example.test',
            'notification_toggles' => EmailNotificationCatalog::defaultEnabledKeys(),
        ]);

        $this->from('/properties/demo-property')
            ->post(route('buyer.verification-code.store'), [
                'buyer_register_email' => 'buyer@example.test',
            ])
            ->assertRedirect('/properties/demo-property')
            ->assertSessionHas('offer_toast', 'A verification code has been sent to your email address.')
            ->assertSessionHas('offer_code_cooldown_seconds', 60)
            ->assertSessionMissing('offer_status');

        Mail::assertSent(TemplatedEmail::class);
    }

    public function test_disabled_notification_is_skipped_and_logged(): void
    {
        Mail::fake();

        EmailSetting::query()->create([
            'mail_enabled' => true,
            'provider' => 'system',
            'from_name' => 'Estate Agents Main',
            'from_email' => 'hello@example.test',
            'notification_toggles' => collect(EmailNotificationCatalog::defaultEnabledKeys())
                ->reject(fn (string $key): bool => $key === 'enquiry_confirmation')
                ->values()
                ->all(),
        ]);

        app(EmailNotificationService::class)->send('enquiry_confirmation', 'buyer@example.test', 'Buyer One', [
            'name' => 'Buyer One',
            'property_title' => 'Demo Property',
            'message' => 'Please send more details.',
            'site_name' => 'Estate Agents Main',
        ]);

        Mail::assertNothingSent();

        $this->assertDatabaseHas(EmailDeliveryLog::class, [
            'template_key' => 'enquiry_confirmation',
            'recipient_email' => 'buyer@example.test',
            'status' => 'skipped',
        ]);
    }

    public function test_buyer_password_reset_uses_email_code_and_signs_buyer_in(): void
    {
        Mail::fake();

        EmailSetting::query()->create([
            'mail_enabled' => true,
            'provider' => 'system',
            'from_name' => 'Estate Agents Main',
            'from_email' => 'hello@example.test',
            'notification_toggles' => EmailNotificationCatalog::defaultEnabledKeys(),
        ]);

        $buyer = User::query()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.test',
            'password' => 'old-password',
            'role' => 'buyer',
        ]);

        $this->post(route('buyer.password-reset-code.store'), [
            'buyer_reset_email' => 'buyer@example.test',
        ])->assertRedirect()
            ->assertSessionHas('offer_status');

        $code = app(BuyerEmailVerificationService::class)->issuePasswordReset('buyer@example.test');

        Mail::assertSent(TemplatedEmail::class, function (TemplatedEmail $mail) use ($code): bool {
            return str_contains($mail->subjectLine, 'password reset code')
                && str_contains($mail->textBody, $code);
        });

        $this->post(route('buyer.password-reset'), [
            'buyer_reset_email' => 'buyer@example.test',
            'buyer_reset_code' => $code,
            'buyer_reset_password' => 'new-secret',
            'buyer_reset_password_confirmation' => 'new-secret',
        ])->assertRedirect()
            ->assertSessionHas('offer_status');

        $this->assertAuthenticatedAs($buyer);
        $this->assertTrue(Hash::check('new-secret', $buyer->refresh()->password));
        $this->assertNotNull($buyer->email_verified_at);

        $this->assertDatabaseHas(EmailDeliveryLog::class, [
            'template_key' => 'password_reset',
            'recipient_email' => 'buyer@example.test',
            'status' => 'sent',
        ]);
    }
}
