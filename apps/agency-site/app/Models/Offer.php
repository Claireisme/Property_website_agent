<?php

namespace App\Models;

use App\Services\EmailNotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'buyer_access_request_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'amount',
        'status',
        'buyer_position',
        'financing_type',
        'mortgage_approval_status',
        'current_property_status',
        'conditions',
        'proof_document_path',
        'message',
        'consent_to_terms',
        'submitted_at',
        'reviewed_at',
        'accepted_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'conditions' => 'array',
            'consent_to_terms' => 'boolean',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Offer $offer): void {
            if (! $offer->wasChanged('status')) {
                return;
            }

            $oldStatus = $offer->getOriginal('status');
            $newStatus = $offer->status;

            $updates = [];

            if (in_array($newStatus, ['pending_review', 'valid', 'request_more_info', 'countered'], true) && blank($offer->reviewed_at)) {
                $updates['reviewed_at'] = now();
            }

            if ($newStatus === 'rejected' && blank($offer->rejected_at)) {
                $updates['rejected_at'] = now();
            }

            if ($newStatus === 'accepted_subject_to_contract' && blank($offer->accepted_at)) {
                $updates['accepted_at'] = now();
            }

            if ($updates !== []) {
                $offer->forceFill($updates)->saveQuietly();
            }

            OfferEvent::query()->create([
                'offer_id' => $offer->id,
                'actor_type' => 'agent',
                'event_type' => 'status_changed',
                'metadata' => [
                    'from' => $oldStatus,
                    'to' => $newStatus,
                    'amount' => $offer->amount,
                ],
            ]);

            if ($newStatus === 'accepted_subject_to_contract') {
                $offer->property?->forceFill([
                    'status' => 'sale_agreed',
                    'sale_agreed_at' => now(),
                ])->save();

                app(EmailNotificationService::class)->sendOfferWon($offer->loadMissing('property.agency'));
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function buyerAccessRequest(): BelongsTo
    {
        return $this->belongsTo(BuyerAccessRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(OfferEvent::class);
    }
}
