<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\BuyerAccessRequests\BuyerAccessRequestResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Offers\OfferResource;
use App\Filament\Resources\ValuationRequests\ValuationRequestResource;
use App\Models\BuyerAccessRequest;
use App\Models\Enquiry;
use App\Models\Offer;
use App\Models\ValuationRequest;
use App\Support\PropertyOptions;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;

class AdminActionQueueWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.admin-action-queue-widget';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'cards' => $this->cards(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function cards(): array
    {
        $cards = [];
        $user = auth()->user();

        if ($user?->can('viewAny', Enquiry::class)) {
            $query = Enquiry::query()->with('property')->where('status', 'new')->latest();
            $count = $query->clone()->count();

            if ($count > 0) {
                $cards[] = [
                    'title' => 'Recent enquiries',
                    'description' => 'Fresh buyer or renter messages.',
                    'count' => $count,
                    'url' => EnquiryResource::getUrl('index'),
                    'action' => 'Open enquiries',
                    'empty' => 'No new enquiries are waiting.',
                    'items' => $query->limit(3)->get()->map(fn (Enquiry $enquiry): array => [
                        'label' => $enquiry->name,
                        'title' => $this->recordTitle($enquiry->property) ?? 'General enquiry',
                        'meta' => $this->timeAgo($enquiry->created_at),
                        'badge' => PropertyOptions::enquiryTypes()[$enquiry->enquiry_type] ?? 'Enquiry',
                        'color' => 'warning',
                        'url' => EnquiryResource::getUrl('view', ['record' => $enquiry->getKey()]),
                    ])->all(),
                ];
            }
        }

        if ($user?->can('viewAny', BuyerAccessRequest::class)) {
            $query = BuyerAccessRequest::query()
                ->with('property')
                ->whereIn('status', ['submitted', 'pending_documents', 'pending_review'])
                ->latest('requested_at');
            $count = $query->clone()->count();

            if ($count > 0) {
                $cards[] = [
                    'title' => 'Buyer access',
                    'description' => 'Registration and document checks.',
                    'count' => $count,
                    'url' => BuyerAccessRequestResource::getUrl('index'),
                    'action' => 'Review access',
                    'empty' => 'No buyer access requests need review.',
                    'items' => $query->limit(3)->get()->map(fn (BuyerAccessRequest $request): array => [
                        'label' => $request->buyer_name,
                        'title' => $this->recordTitle($request->property) ?? 'Property access request',
                        'meta' => $this->timeAgo($request->requested_at ?? $request->created_at),
                        'badge' => PropertyOptions::buyerAccessStatuses()[$request->status] ?? ucfirst($request->status),
                        'color' => $this->statusColor($request->status),
                        'url' => BuyerAccessRequestResource::getUrl('view', ['record' => $request->getKey()]),
                    ])->all(),
                ];
            }
        }

        if ($user?->can('viewAny', Offer::class)) {
            $query = Offer::query()
                ->with('property')
                ->whereIn('status', ['submitted', 'pending_review', 'request_more_info', 'countered'])
                ->latest('submitted_at');
            $count = $query->clone()->count();

            if ($count > 0) {
                $cards[] = [
                    'title' => 'Offers',
                    'description' => 'Buyer bids and follow-up items.',
                    'count' => $count,
                    'url' => OfferResource::getUrl('index'),
                    'action' => 'Open offers',
                    'empty' => 'No offers need action right now.',
                    'items' => $query->limit(3)->get()->map(fn (Offer $offer): array => [
                        'label' => $offer->buyer_name,
                        'title' => $this->recordTitle($offer->property) ?? 'Offer submitted',
                        'meta' => $this->timeAgo($offer->submitted_at ?? $offer->created_at),
                        'badge' => PropertyOptions::offerStatuses()[$offer->status] ?? ucfirst($offer->status),
                        'color' => $this->statusColor($offer->status),
                        'amount' => 'EUR '.number_format($offer->amount),
                        'url' => OfferResource::getUrl('view', ['record' => $offer->getKey()]),
                    ])->all(),
                ];
            }
        }

        if ($user?->can('viewAny', ValuationRequest::class)) {
            $query = ValuationRequest::query()->where('status', 'new')->latest();
            $count = $query->clone()->count();

            if ($count > 0) {
                $cards[] = [
                    'title' => 'Valuations',
                    'description' => 'New seller leads from the site.',
                    'count' => $count,
                    'url' => ValuationRequestResource::getUrl('index'),
                    'action' => 'Open valuations',
                    'empty' => 'No valuation requests are waiting.',
                    'items' => $query->limit(3)->get()->map(fn (ValuationRequest $request): array => [
                        'label' => $request->name,
                        'title' => $request->property_address ?: 'Valuation request',
                        'meta' => $this->timeAgo($request->created_at),
                        'badge' => PropertyOptions::leadStatuses()[$request->status] ?? ucfirst($request->status),
                        'color' => $this->statusColor($request->status),
                        'url' => ValuationRequestResource::getUrl('view', ['record' => $request->getKey()]),
                    ])->all(),
                ];
            }
        }

        return $cards;
    }

    private function recordTitle(?Model $record): ?string
    {
        if ($record === null) {
            return null;
        }

        return $record->title ?? $record->name ?? null;
    }

    private function timeAgo(mixed $date): string
    {
        return $date?->diffForHumans() ?? 'No date recorded';
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'submitted', 'pending_documents', 'pending_review', 'new' => 'warning',
            'request_more_info', 'countered', 'contacted' => 'info',
            'approved', 'valid', 'accepted_subject_to_contract', 'closed' => 'success',
            'rejected', 'spam' => 'danger',
            default => 'gray',
        };
    }
}
