<x-layouts.site :title="config('app.name')">
    <section class="hero">
        <div class="shell">
            <span class="badge">{{ __('site.portal.badge') }}</span>
            <h1>{{ config('app.name') }}</h1>
            <p class="lead">{{ __('site.portal.hero_copy') }}</p>
            <form class="search" method="GET" action="{{ \App\Support\LocaleUrl::route('properties.index') }}">
                <input name="q" placeholder="{{ __('site.properties.search_placeholder') }}">
                <select name="type">
                    <option value="">{{ __('site.properties.all_types') }}</option>
                    <option value="house">{{ __('site.types.house') }}</option>
                    <option value="apartment">{{ __('site.types.apartment') }}</option>
                    <option value="semi_detached">{{ __('site.types.semi_detached') }}</option>
                    <option value="detached">{{ __('site.types.detached') }}</option>
                    <option value="terraced">{{ __('site.types.terraced') }}</option>
                </select>
                <button type="submit">{{ __('site.actions.search') }}</button>
            </form>
        </div>
    </section>

    <section class="band">
        <div class="shell">
            <h2>{{ __('site.portal.latest_listings') }}</h2>
            <div class="grid">
                @forelse ($properties as $property)
                    @include('properties.partials.card', ['property' => $property])
                @empty
                    <p class="muted">{{ __('site.portal.empty_listings') }}</p>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.site>
