<article class="card">
    <a href="{{ \App\Support\LocaleUrl::route('properties.show', ['property' => $property]) }}">
        <div class="photo">
            @if ($image = $property->images->first())
                <img src="{{ $image->publicUrl($image->card_url ?: $image->original_url) }}" alt="{{ $image->caption ?: $property->localizedTitle() }}" loading="lazy" decoding="async">
            @else
                <span class="photo-placeholder">{{ $property->town ?: $property->county ?: __('site.properties.property_fallback') }}</span>
            @endif
            <span class="status-corner {{ $property->status }}"><span>{{ str_replace('_', ' ', $property->status) }}</span></span>
        </div>
        <div class="card-body">
            <span class="badge">{{ \App\Support\PropertyOptions::listingCategories()[$property->listingCategory()] ?? str_replace('_', ' ', $property->transaction_type) }}</span>
            <h3 style="margin: 12px 0 0;">{{ $property->localizedTitle() }}</h3>
            <div class="meta">
                @if ($property->bedrooms !== null)
                    <span>{{ $property->bedrooms }} {{ __('site.labels.beds') }}</span>
                @endif
                @if ($property->bathrooms !== null)
                    <span>{{ $property->bathrooms }} {{ __('site.labels.baths') }}</span>
                @endif
                @if ($property->town || $property->county)
                    <span>{{ collect([$property->town, $property->county])->filter()->join(', ') }}</span>
                @endif
            </div>
            <div class="price">
                @if ($property->price)
                    EUR {{ number_format($property->price) }}
                @else
                    {{ __('site.properties.price_on_application') }}
                @endif
            </div>
        </div>
    </a>
</article>
