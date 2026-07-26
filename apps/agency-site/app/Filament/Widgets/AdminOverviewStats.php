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
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminOverviewStats extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    protected ?string $heading = 'Today at a glance';

    protected ?string $description = 'Live queues from enquiries, buyer access, offers, and valuation requests.';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = [];
        $user = auth()->user();

        if ($user?->can('viewAny', Enquiry::class)) {
            $newEnquiries = Enquiry::query()->where('status', 'new')->count();
            $todayEnquiries = Enquiry::query()->whereDate('created_at', today())->count();

            $stats[] = Stat::make('New enquiries', number_format($newEnquiries))
                ->description($todayEnquiries.' received today')
                ->descriptionColor($newEnquiries > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color($newEnquiries > 0 ? 'warning' : 'gray')
                ->url(EnquiryResource::getUrl('index'));
        }

        if ($user?->can('viewAny', BuyerAccessRequest::class)) {
            $documentChecks = BuyerAccessRequest::query()
                ->whereIn('status', ['submitted', 'pending_documents', 'pending_review'])
                ->count();

            $stats[] = Stat::make('Buyer access checks', number_format($documentChecks))
                ->description('Documents and bidding access awaiting review')
                ->descriptionColor($documentChecks > 0 ? 'warning' : 'gray')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color($documentChecks > 0 ? 'warning' : 'gray')
                ->url(BuyerAccessRequestResource::getUrl('index'));
        }

        if ($user?->can('viewAny', Offer::class)) {
            $offersToReview = Offer::query()
                ->whereIn('status', ['submitted', 'pending_review', 'request_more_info', 'countered'])
                ->count();

            $stats[] = Stat::make('Offers to review', number_format($offersToReview))
                ->description('Buyer bids that may need agent action')
                ->descriptionColor($offersToReview > 0 ? 'success' : 'gray')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color($offersToReview > 0 ? 'success' : 'gray')
                ->url(OfferResource::getUrl('index'));
        }

        if ($user?->can('viewAny', ValuationRequest::class)) {
            $newValuations = ValuationRequest::query()->where('status', 'new')->count();

            $stats[] = Stat::make('Valuation requests', number_format($newValuations))
                ->description('Fresh seller leads waiting for follow-up')
                ->descriptionColor($newValuations > 0 ? 'info' : 'gray')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color($newValuations > 0 ? 'info' : 'gray')
                ->url(ValuationRequestResource::getUrl('index'));
        }

        if ($stats === []) {
            $stats[] = Stat::make('Dashboard', 'Ready')
                ->description('No workflow queues are available for this account.')
                ->color('gray');
        }

        return $stats;
    }
}
