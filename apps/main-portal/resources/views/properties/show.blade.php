@php
    $image = collect($property->images)->first();
@endphp

<x-layouts.site :title="$property->localizedTitle()">
    <section class="hero">
        <div class="shell">
            <span class="badge">{{ str_replace('_', ' ', $property->status) }}</span>
            <h1>{{ $property->localizedTitle() }}</h1>
            <p class="lead">{{ $property->address_summary }} · {{ __('site.portal.listed_by', ['agency' => $property->agency->name]) }}</p>
            @if ($property->online_offers_enabled)
                <p class="lead">{{ __('site.portal.online_offers_enabled') }}</p>
            @endif
            <div class="price" style="color: #ffffff;">
                @if ($property->price)
                    EUR {{ number_format($property->price) }}
                @else
                    {{ __('site.properties.price_on_application') }}
                @endif
            </div>
        </div>
    </section>

    <section class="band">
        <div class="shell" style="display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 28px;">
            <article>
                <div class="photo" style="aspect-ratio: 16 / 9; margin-bottom: 28px;">
                    @if ($image && filled($image['url'] ?? null))
                        <img src="{{ $image['url'] }}" alt="{{ $image['caption'] ?? $property->localizedTitle() }}">
                    @else
                        <span>{{ $property->town ?: $property->county ?: __('site.properties.property_fallback') }}</span>
                    @endif
                </div>

                <h2>{{ __('site.properties.description') }}</h2>
                <p class="muted">{{ $property->localizedDescription() }}</p>

                @if ($property->localizedFeatures())
                    <h2 style="margin-top: 34px;">{{ __('site.properties.features') }}</h2>
                    <div class="grid">
                        @foreach ($property->localizedFeatures() as $feature)
                            <div class="card"><div class="card-body">{{ $feature }}</div></div>
                        @endforeach
                    </div>
                @endif
            </article>

            <aside class="card">
                <div class="card-body">
                    <h2>{{ __('site.properties.listing_details') }}</h2>
                    <div class="meta">
                        @if ($property->bedrooms !== null)
                            <span>{{ $property->bedrooms }} {{ __('site.labels.bedrooms') }}</span>
                        @endif
                        @if ($property->bathrooms !== null)
                            <span>{{ $property->bathrooms }} {{ __('site.labels.bathrooms') }}</span>
                        @endif
                        @if ($property->floor_area_m2)
                            <span>{{ $property->floor_area_m2 }} m2</span>
                        @endif
                        @if ($property->ber_rating)
                            <span>BER {{ $property->ber_rating }}</span>
                        @endif
                    </div>
                    @if ($property->source_url)
                        <a class="button" href="{{ $property->source_url }}">{{ __('site.actions.view_on_agency_site') }}</a>
                    @endif

                    <h2 style="margin-top: 28px;">{{ __('site.properties.enquire') }}</h2>
                    @if (session('status'))
                        <div style="border: 1px solid #9bd6ca; background: #edf9f6; color: #14554f; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                            {{ session('status') }}
                        </div>
                    @endif
                    <form method="POST" action="{{ \App\Support\LocaleUrl::route('properties.enquiries.store', ['property' => $property]) }}" style="display: grid; gap: 12px;">
                        @csrf
                        <label>{{ __('site.labels.name') }}
                            <input name="name" value="{{ old('name') }}" required>
                        </label>
                        <label>{{ __('site.labels.email') }}
                            <input name="email" type="email" value="{{ old('email') }}" required>
                        </label>
                        <label>{{ __('site.labels.phone') }}
                            <input name="phone" value="{{ old('phone') }}">
                        </label>
                        <label>{{ __('site.labels.message') }}
                            <textarea name="message" rows="5">{{ old('message') }}</textarea>
                        </label>
                        <button type="submit">{{ __('site.actions.send_enquiry') }}</button>
                    </form>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
