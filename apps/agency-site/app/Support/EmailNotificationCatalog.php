<?php

namespace App\Support;

use App\Models\EmailNotificationTemplate;

class EmailNotificationCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function templates(): array
    {
        return [
            [
                'key' => 'buyer_email_verification',
                'label' => 'Buyer account email verification code',
                'audience' => 'buyer',
                'subject' => 'Your {{ site_name }} verification code',
                'body' => "Hello,\n\nYour verification code is {{ code }}.\n\nIt expires in {{ expires_minutes }} minutes. If you did not request this, you can ignore this email.",
                'variables' => ['site_name', 'code', 'expires_minutes'],
            ],
            [
                'key' => 'buyer_access_submitted',
                'label' => 'Buyer bidding access submitted',
                'audience' => 'buyer',
                'subject' => 'We received your bidding access request for {{ property_title }}',
                'body' => "Hello {{ buyer_name }},\n\nWe received your bidding access request for {{ property_title }} with an initial offer of {{ offer_amount }}.\n\nThe agency will review your documents and let you know the next step.",
                'variables' => ['buyer_name', 'property_title', 'offer_amount', 'site_name'],
            ],
            [
                'key' => 'buyer_access_agent_alert',
                'label' => 'Agent alert: buyer access needs review',
                'audience' => 'agent',
                'subject' => 'Buyer access request needs review: {{ property_title }}',
                'body' => "{{ buyer_name }} submitted a buyer access request for {{ property_title }}.\n\nInitial offer: {{ offer_amount }}\nEmail: {{ buyer_email }}\nPhone: {{ buyer_phone }}\n\nPlease review the documents in the admin dashboard.",
                'variables' => ['buyer_name', 'buyer_email', 'buyer_phone', 'property_title', 'offer_amount'],
            ],
            [
                'key' => 'buyer_access_approved',
                'label' => 'Buyer bidding access approved',
                'audience' => 'buyer',
                'subject' => 'Your bidding access has been approved',
                'body' => "Hello {{ buyer_name }},\n\nYour bidding access for {{ property_title }} has been approved.\n\nYour current offer is {{ offer_amount }}. You can sign in to continue from the property page.",
                'variables' => ['buyer_name', 'property_title', 'offer_amount'],
            ],
            [
                'key' => 'buyer_access_rejected',
                'label' => 'Buyer bidding access rejected',
                'audience' => 'buyer',
                'subject' => 'Update on your bidding access request',
                'body' => "Hello {{ buyer_name }},\n\nYour bidding access request for {{ property_title }} was not approved at this time.\n\nPlease contact the agency if you need more information.",
                'variables' => ['buyer_name', 'property_title'],
            ],
            [
                'key' => 'offer_submitted',
                'label' => 'Buyer offer submitted confirmation',
                'audience' => 'buyer',
                'subject' => 'Your offer for {{ property_title }} has been submitted',
                'body' => "Hello {{ buyer_name }},\n\nYour offer of {{ offer_amount }} for {{ property_title }} has been submitted successfully.\n\nThe agency will review and follow up.",
                'variables' => ['buyer_name', 'property_title', 'offer_amount'],
            ],
            [
                'key' => 'offer_agent_alert',
                'label' => 'Agent alert: new buyer offer',
                'audience' => 'agent',
                'subject' => 'New offer submitted: {{ property_title }}',
                'body' => "{{ buyer_name }} submitted an offer of {{ offer_amount }} for {{ property_title }}.\n\nEmail: {{ buyer_email }}\nPhone: {{ buyer_phone }}\n\nPlease review it in the admin dashboard.",
                'variables' => ['buyer_name', 'buyer_email', 'buyer_phone', 'property_title', 'offer_amount'],
            ],
            [
                'key' => 'offer_won',
                'label' => 'Buyer final successful bid',
                'audience' => 'buyer',
                'subject' => 'Your bid was successful',
                'body' => "Hello {{ buyer_name }},\n\nCongratulations. Your bid for {{ property_title }} was successful.\n\nThe agency will contact you with the next steps.",
                'variables' => ['buyer_name', 'property_title', 'offer_amount'],
            ],
            [
                'key' => 'enquiry_confirmation',
                'label' => 'Property enquiry confirmation',
                'audience' => 'buyer',
                'subject' => 'We received your enquiry for {{ property_title }}',
                'body' => "Hello {{ name }},\n\nThanks for your enquiry about {{ property_title }}. The agency team will respond as soon as possible.\n\nYour message:\n{{ message }}",
                'variables' => ['name', 'property_title', 'message', 'site_name'],
            ],
            [
                'key' => 'enquiry_agent_alert',
                'label' => 'Agent alert: new property enquiry',
                'audience' => 'agent',
                'subject' => 'New enquiry: {{ property_title }}',
                'body' => "{{ name }} sent a {{ enquiry_type }} enquiry for {{ property_title }}.\n\nEmail: {{ email }}\nPhone: {{ phone }}\n\nMessage:\n{{ message }}",
                'variables' => ['name', 'email', 'phone', 'enquiry_type', 'property_title', 'message'],
            ],
            [
                'key' => 'valuation_confirmation',
                'label' => 'Valuation request confirmation',
                'audience' => 'seller',
                'subject' => 'We received your valuation request',
                'body' => "Hello {{ name }},\n\nThanks for requesting a valuation for {{ property_address }}. The agency team will follow up with you shortly.",
                'variables' => ['name', 'property_address', 'site_name'],
            ],
            [
                'key' => 'valuation_agent_alert',
                'label' => 'Agent alert: new valuation request',
                'audience' => 'agent',
                'subject' => 'New valuation request: {{ property_address }}',
                'body' => "{{ name }} requested a valuation.\n\nAddress: {{ property_address }}\nEmail: {{ email }}\nPhone: {{ phone }}\nTimeline: {{ selling_timeline }}",
                'variables' => ['name', 'property_address', 'email', 'phone', 'selling_timeline'],
            ],
            [
                'key' => 'password_reset',
                'label' => 'Buyer password reset',
                'audience' => 'buyer',
                'subject' => 'Your {{ site_name }} password reset code',
                'body' => "Hello,\n\nYour password reset code is {{ code }}.\n\nIt expires in {{ expires_minutes }} minutes. If you did not request this, you can ignore this email.",
                'variables' => ['site_name', 'code', 'expires_minutes'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function template(string $key): ?array
    {
        return collect(self::templates())->firstWhere('key', $key);
    }

    /**
     * @return array<int, string>
     */
    public static function defaultEnabledKeys(): array
    {
        return collect(self::templates())->pluck('key')->all();
    }

    /**
     * @return array<string, string>
     */
    public static function toggleOptions(): array
    {
        return collect(self::templates())
            ->mapWithKeys(fn (array $template): array => [$template['key'] => $template['label']])
            ->all();
    }

    public static function syncDefaults(): void
    {
        foreach (self::templates() as $template) {
            $record = EmailNotificationTemplate::query()->firstOrCreate([
                'key' => $template['key'],
            ], [
                'label' => $template['label'],
                'audience' => $template['audience'],
                'subject' => $template['subject'],
                'body' => $template['body'],
                'available_variables' => $template['variables'],
            ]);

            $record->forceFill([
                'label' => $template['label'],
                'audience' => $template['audience'],
                'available_variables' => $template['variables'],
            ])->saveQuietly();
        }
    }
}
