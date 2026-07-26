<?php

namespace App\Providers;

use App\Models\Agency;
use App\Models\BuyerAccessRequest;
use App\Models\Enquiry;
use App\Models\FeedToken;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Policies\AdminOnlyPolicy;
use App\Policies\EnquiryPolicy;
use App\Policies\PropertyImagePolicy;
use App\Policies\PropertyPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(PropertyImage::class, PropertyImagePolicy::class);
        Gate::policy(Enquiry::class, EnquiryPolicy::class);

        foreach ([
            Agency::class,
            BuyerAccessRequest::class,
            FeedToken::class,
            Offer::class,
            OfferEvent::class,
            TeamMember::class,
            ValuationRequest::class,
        ] as $model) {
            Gate::policy($model, AdminOnlyPolicy::class);
        }

        Gate::before(function (User $user): ?bool {
            return $user->isAdministrator() ? true : null;
        });
    }
}
