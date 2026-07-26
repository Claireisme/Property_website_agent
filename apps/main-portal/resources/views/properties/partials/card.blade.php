@php
    $image = collect($property->images)->first();
@endphp

<article class="card">
    <a href="{{ \App\Support\LocaleUrl::route('properties.show', ['property' => $property]) }}">
        <div class="photo">
            @if ($image && filled($image['url'] ?? null))
                <img src="{{ $image['url'] }}" alt="{{ $image['caption'] ?? $property->localizedTitle() }}">
            @else
                <span>{{ $property->town ?: $property->county ?: __('site.properties.property_fallback') }}</span>
            @endif
            <span class="status-corner {{ $property->status }}"><span>{{ str_replace('_', ' ', $property->status) }}</span></span>
        </div>
        <div class="card-body">
            <span class="badge">{{ str_replace('_', ' ', $property->status) }}</span>
            <h3 style="margin: 12px 0 0;">{{ $property->localizedTitle() }}</h3>
            <div class="meta">
                @if ($property->bedrooms !== null)
                    <span>{{ $property->bedrooms }} {{ __('site.labels.beds') }}</span>
                @endif
                @if ($property->bathrooms !== null)
                    <span>{{ $property->bathrooms }} {{ __('site.labels.baths') }}</span>
                @endif
                @if ($property->address_summary)
                    <span>{{ $property->address_summary }}</span>
                @endif
                <span>{{ $property->agency->name }}</span>
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
