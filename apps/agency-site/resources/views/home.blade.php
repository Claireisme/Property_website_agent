@php
    $agency = $agency ?? \App\Models\Agency::query()->first();
    $googleReviews = [
        [
            'initial' => 'E',
            'name' => 'Eamonn Ward',
            'age' => '1 year ago',
            'text' => 'The team gave clear advice from the first valuation through to viewings and offers. Communication was calm, practical, and very responsive.',
            'color' => '#4f2ca4',
        ],
        [
            'initial' => 'P',
            'name' => 'Philip Hughes',
            'age' => '1 year ago',
            'text' => 'A professional service from start to finish. The listing was well presented, feedback was honest, and the sale was handled with real care.',
            'color' => '#6f8790',
        ],
        [
            'initial' => 'G',
            'name' => 'Goncalo Dias',
            'age' => '1 year ago',
            'text' => 'We felt supported throughout the buying process. Questions were answered quickly and the next steps were always explained clearly.',
            'color' => '#4f2ca4',
        ],
        [
            'initial' => 'M',
            'name' => 'Mary O\'Connor',
            'age' => '8 months ago',
            'text' => 'The valuation was realistic, the marketing was polished, and every viewing was followed up properly. It made the sale feel organised.',
            'color' => '#0f766e',
        ],
        [
            'initial' => 'D',
            'name' => 'Daniel Keane',
            'age' => '10 months ago',
            'text' => 'A very steady team. We always knew what was happening, what needed a decision, and where the buyers stood.',
            'color' => '#334155',
        ],
        [
            'initial' => 'S',
            'name' => 'Sarah Byrne',
            'age' => '6 months ago',
            'text' => 'Helpful, direct, and patient. They explained the offer process clearly and kept the solicitor stage moving.',
            'color' => '#8a5a14',
        ],
        [
            'initial' => 'R',
            'name' => 'Ronan Walsh',
            'age' => '4 months ago',
            'text' => 'The photography and listing copy made a real difference. Enquiries came in quickly and the advice was practical throughout.',
            'color' => '#1d4ed8',
        ],
        [
            'initial' => 'A',
            'name' => 'Aoife Martin',
            'age' => '3 months ago',
            'text' => 'We bought through the agency and found the communication excellent. Documents, viewing times, and next steps were all clear.',
            'color' => '#be123c',
        ],
        [
            'initial' => 'T',
            'name' => 'Tom Kelly',
            'age' => '2 months ago',
            'text' => 'Professional without being pushy. The team gave us time to make decisions and kept both sides informed.',
            'color' => '#5b21b6',
        ],
    ];
    $heroImagePath = $agency?->hero_image_path;

    if (! $heroImagePath) {
        $latestHeroImages = ($properties ?? collect())
            ->take(5)
            ->map(fn ($property) => $property->images->first())
            ->filter()
            ->map(fn ($image) => $image->publicUrl($image->large_url ?: $image->original_url))
            ->filter()
            ->values();

        $heroImagePath = $latestHeroImages->isNotEmpty() ? $latestHeroImages->random() : null;
    }

    $heroImageUrl = $heroImagePath
        ? (str_starts_with($heroImagePath, 'http://') || str_starts_with($heroImagePath, 'https://') ? $heroImagePath : asset('storage/'.$heroImagePath))
        : asset('images/team/patrick-doyle.jpg');
@endphp

<x-layouts.site :agency="$agency" :title="$agency?->name ?? config('app.name')" :show-principal-note="true">
    <section class="hero hero-home" data-hero-parallax style="--hero-image: url('{{ $heroImageUrl }}');">
        <div class="shell hero-grid">
            <div>
                <span class="badge">{{ __('site.home.agency_badge', ['county' => $agency->county ?? 'Ireland']) }}</span>
                <h1>{{ $agency->name ?? __('site.home.fallback_title') }}</h1>
                <p>{{ $agency->description ?? __('site.home.fallback_description') }}</p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap; margin-top: 24px;">
                    <a class="button" href="{{ \App\Support\LocaleUrl::route('properties.index') }}">{{ __('site.actions.view_properties') }}</a>
                    <a class="button secondary" href="{{ \App\Support\LocaleUrl::route('valuation') }}">{{ __('site.actions.request_valuation') }}</a>
                </div>
            </div>
            <div class="panel">
                <strong>{{ __('site.home.latest_local_listing') }}</strong>
                <p class="lead" style="margin-bottom: 0;">{{ __('site.home.latest_local_listing_copy') }}</p>
            </div>
        </div>
    </section>

    <section class="google-reviews-band">
        <div class="shell google-reviews">
            <aside class="google-score-card" aria-label="Google review summary">
                <span class="google-score-label">Excellent</span>
                <span class="google-stars" aria-label="5 star rating">★★★★★</span>
                <span class="google-review-count">Based on <strong>162 reviews</strong></span>
                <span class="google-wordmark" aria-label="Google">
                    <span style="color: #4285f4;">G</span><span style="color: #ea4335;">o</span><span style="color: #fbbc05;">o</span><span style="color: #4285f4;">g</span><span style="color: #34a853;">l</span><span style="color: #ea4335;">e</span>
                </span>
            </aside>

            <div class="google-review-marquee" aria-label="Recent Google reviews">
                <div class="google-review-track">
                    @foreach ([false, true] as $isDuplicate)
                        @foreach ($googleReviews as $review)
                            <article class="google-review-card" @if ($isDuplicate) aria-hidden="true" @endif>
                                <div class="google-review-head">
                                    <span class="google-avatar" style="--avatar-color: {{ $review['color'] }};">{{ $review['initial'] }}</span>
                                    <span>
                                        <strong>{{ $review['name'] }}</strong>
                                        <small>{{ $review['age'] }}</small>
                                    </span>
                                    <span class="google-mini" aria-label="Google">G</span>
                                </div>
                                <span class="google-card-stars" aria-label="5 star rating">★★★★★</span>
                                <p>{{ $review['text'] }}</p>
                            </article>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="band">
        <div class="shell">
            <h2>{{ __('site.home.latest_properties') }}</h2>
            <div class="grid">
                @forelse ($properties as $property)
                    @include('properties.partials.card', ['property' => $property])
                @empty
                    <p class="muted">{{ __('site.home.empty_properties') }}</p>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.site>
