<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuyerAccessRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'buyer_name',
        'buyer_email',
        'buyer_phone',
        'status',
        'initial_offer_amount',
        'buyer_position',
        'financing_type',
        'mortgage_approval_status',
        'current_property_status',
        'proof_of_funds_path',
        'identity_document_path',
        'message',
        'consent_to_terms',
        'requested_at',
        'documents_uploaded_at',
        'reviewed_at',
        'approved_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'initial_offer_amount' => 'integer',
            'consent_to_terms' => 'boolean',
            'requested_at' => 'datetime',
            'documents_uploaded_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (BuyerAccessRequest $request): void {
            if (! $request->wasChanged('status')) {
                return;
            }

            $updates = [];

            if (in_array($request->status, ['pending_review', 'approved', 'rejected'], true) && blank($request->reviewed_at)) {
                $updates['reviewed_at'] = now();
            }

            if ($request->status === 'approved' && blank($request->approved_at)) {
                $updates['approved_at'] = now();
            }

            if ($request->status === 'rejected' && blank($request->rejected_at)) {
                $updates['rejected_at'] = now();
            }

            if ($updates !== []) {
                $request->forceFill($updates)->saveQuietly();
            }
        });
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }
}
