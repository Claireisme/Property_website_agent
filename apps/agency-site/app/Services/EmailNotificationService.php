<?php

namespace App\Services;

use App\Mail\TemplatedEmail;
use App\Models\Agency;
use App\Models\BuyerAccessRequest;
use App\Models\EmailDeliveryLog;
use App\Models\EmailNotificationTemplate;
use App\Models\EmailSetting;
use App\Models\Enquiry;
use App\Models\Offer;
use App\Models\Property;
use App\Models\ValuationRequest;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class EmailNotificationService
{
    public function send(
        string $templateKey,
        string $recipientEmail,
        ?string $recipientName = null,
        array $variables = [],
        array $metadata = [],
        ?string $replyToEmail = null,
    ): EmailDeliveryLog {
        $setting = EmailSetting::current();
        $template = EmailNotificationTemplate::forKey($templateKey);
        $subject = $this->render($template->subject, $variables);
        $textBody = $this->render($template->body, $variables);

        if (! $setting->mail_enabled || ! $setting->notificationIsEnabled($templateKey) || ! $template->is_enabled) {
            return EmailDeliveryLog::query()->create([
                'template_key' => $templateKey,
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'subject' => $subject,
                'status' => 'skipped',
                'metadata' => $metadata + ['reason' => 'disabled'],
            ]);
        }

        try {
            $setting->applyMailConfig();

            Mail::to($recipientEmail, $recipientName)->send(new TemplatedEmail(
                $subject,
                $this->toHtml($textBody),
                $textBody,
                $replyToEmail ?: $setting->reply_to_email,
            ));

            return EmailDeliveryLog::query()->create([
                'template_key' => $templateKey,
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'subject' => $subject,
                'status' => 'sent',
                'metadata' => $metadata,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return EmailDeliveryLog::query()->create([
                'template_key' => $templateKey,
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'subject' => $subject,
                'status' => 'failed',
                'error' => Str::limit($exception->getMessage(), 1000, ''),
                'metadata' => $metadata,
            ]);
        }
    }

    public function sendBuyerEmailVerification(string $email, string $code, int $expiresMinutes): void
    {
        $this->send('buyer_email_verification', $email, null, [
            'site_name' => $this->siteName(),
            'code' => $code,
            'expires_minutes' => $expiresMinutes,
        ], ['email' => $email]);
    }

    public function sendBuyerPasswordReset(string $email, string $code, int $expiresMinutes): void
    {
        $this->send('password_reset', $email, null, [
            'site_name' => $this->siteName(),
            'code' => $code,
            'expires_minutes' => $expiresMinutes,
        ], ['email' => $email]);
    }

    public function sendBuyerAccessSubmitted(BuyerAccessRequest $request): void
    {
        $variables = $this->buyerAccessVariables($request);

        $this->send('buyer_access_submitted', $request->buyer_email, $request->buyer_name, $variables, [
            'buyer_access_request_id' => $request->id,
        ]);

        foreach ($this->agentRecipients($request->property) as $recipient) {
            $this->send('buyer_access_agent_alert', $recipient['email'], $recipient['name'], $variables, [
                'buyer_access_request_id' => $request->id,
                'property_id' => $request->property_id,
            ], $request->buyer_email);
        }
    }

    public function sendBuyerAccessApproved(BuyerAccessRequest $request): void
    {
        $this->send('buyer_access_approved', $request->buyer_email, $request->buyer_name, $this->buyerAccessVariables($request), [
            'buyer_access_request_id' => $request->id,
        ]);
    }

    public function sendBuyerAccessRejected(BuyerAccessRequest $request): void
    {
        $this->send('buyer_access_rejected', $request->buyer_email, $request->buyer_name, $this->buyerAccessVariables($request), [
            'buyer_access_request_id' => $request->id,
        ]);
    }

    public function sendOfferSubmitted(Offer $offer): void
    {
        $variables = [
            'site_name' => $this->siteName($offer->property),
            'buyer_name' => $offer->buyer_name,
            'buyer_email' => $offer->buyer_email,
            'buyer_phone' => $offer->buyer_phone ?: '-',
            'property_title' => $offer->property?->title ?: 'the property',
            'offer_amount' => $this->money($offer->amount),
        ];

        $this->send('offer_submitted', $offer->buyer_email, $offer->buyer_name, $variables, [
            'offer_id' => $offer->id,
            'property_id' => $offer->property_id,
        ]);

        foreach ($this->agentRecipients($offer->property) as $recipient) {
            $this->send('offer_agent_alert', $recipient['email'], $recipient['name'], $variables, [
                'offer_id' => $offer->id,
                'property_id' => $offer->property_id,
            ], $offer->buyer_email);
        }
    }

    public function sendOfferWon(Offer $offer): void
    {
        $this->send('offer_won', $offer->buyer_email, $offer->buyer_name, [
            'site_name' => $this->siteName($offer->property),
            'buyer_name' => $offer->buyer_name,
            'property_title' => $offer->property?->title ?: 'the property',
            'offer_amount' => $this->money($offer->amount),
        ], [
            'offer_id' => $offer->id,
            'property_id' => $offer->property_id,
        ]);
    }

    public function sendEnquiryReceived(Enquiry $enquiry): void
    {
        $propertyTitle = $enquiry->property?->title ?: 'your enquiry';
        $variables = [
            'site_name' => $this->siteName($enquiry->property),
            'name' => $enquiry->name,
            'email' => $enquiry->email,
            'phone' => $enquiry->phone ?: '-',
            'enquiry_type' => $enquiry->enquiry_type ?: 'question',
            'property_title' => $propertyTitle,
            'message' => $enquiry->message ?: '-',
        ];

        $this->send('enquiry_confirmation', $enquiry->email, $enquiry->name, $variables, [
            'enquiry_id' => $enquiry->id,
            'property_id' => $enquiry->property_id,
        ]);

        foreach ($this->agentRecipients($enquiry->property) as $recipient) {
            $this->send('enquiry_agent_alert', $recipient['email'], $recipient['name'], $variables, [
                'enquiry_id' => $enquiry->id,
                'property_id' => $enquiry->property_id,
            ], $enquiry->email);
        }
    }

    public function sendValuationReceived(ValuationRequest $request): void
    {
        $variables = [
            'site_name' => $this->siteName(),
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone ?: '-',
            'property_address' => $request->property_address,
            'selling_timeline' => $request->selling_timeline ?: '-',
        ];

        $this->send('valuation_confirmation', $request->email, $request->name, $variables, [
            'valuation_request_id' => $request->id,
        ]);

        foreach ($this->agentRecipients(null) as $recipient) {
            $this->send('valuation_agent_alert', $recipient['email'], $recipient['name'], $variables, [
                'valuation_request_id' => $request->id,
            ], $request->email);
        }
    }

    private function render(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{{ '.$key.' }}', (string) $value, $template);
            $template = str_replace('{{'.$key.'}}', (string) $value, $template);
        }

        return $template;
    }

    private function toHtml(string $body): string
    {
        return '<div style="font-family:Arial,sans-serif;font-size:16px;line-height:1.55;color:#111827;">'
            .nl2br(e($body))
            .'</div>';
    }

    private function money(?int $amount): string
    {
        if (! $amount) {
            return '-';
        }

        return 'EUR '.number_format($amount);
    }

    /**
     * @return array<int, array{name: string|null, email: string}>
     */
    private function agentRecipients(?Property $property): array
    {
        $emails = collect();

        if ($property?->relationLoaded('teamMember') || $property?->team_member_id) {
            $teamMember = $property->teamMember;

            if ($teamMember?->email) {
                $emails->push(['name' => $teamMember->name, 'email' => $teamMember->email]);
            }
        }

        $agency = $property?->agency ?: Agency::query()->first();

        if ($agency?->email) {
            $emails->push(['name' => $agency->name, 'email' => $agency->email]);
        }

        return $emails
            ->filter(fn (array $recipient): bool => filled($recipient['email']))
            ->unique('email')
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function buyerAccessVariables(BuyerAccessRequest $request): array
    {
        return [
            'site_name' => $this->siteName($request->property),
            'buyer_name' => $request->buyer_name,
            'buyer_email' => $request->buyer_email,
            'buyer_phone' => $request->buyer_phone ?: '-',
            'property_title' => $request->property?->title ?: 'the property',
            'offer_amount' => $this->money($request->initial_offer_amount),
        ];
    }

    private function siteName(?Property $property = null): string
    {
        return $property?->agency?->name
            ?: Agency::query()->value('name')
            ?: config('app.name', 'Estate Agency');
    }
}
