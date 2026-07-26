<x-layouts.site :title="__('site.properties.title')">
    <section class="band">
        <div class="shell">
            <h1 style="font-size: 42px;">{{ __('site.properties.title') }}</h1>
            <p class="muted">{{ __('site.portal.browse_copy') }}</p>

            <form class="search" method="GET" action="{{ \App\Support\LocaleUrl::route('properties.index') }}">
                <input name="q" value="{{ request('q') }}" placeholder="{{ __('site.properties.search_short_placeholder') }}">
                <select name="type">
                    <option value="">{{ __('site.properties.all_types') }}</option>
                    @foreach (['house', 'apartment', 'semi_detached', 'detached', 'terraced', 'bungalow', 'site'] as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ __('site.types.'.$type) }}</option>
                    @endforeach
                </select>
                <button type="submit">{{ __('site.actions.search') }}</button>
            </form>

            <div class="grid" style="margin-top: 26px;">
                @forelse ($properties as $property)
                    @include('properties.partials.card', ['property' => $property])
                @empty
                    <p class="muted">{{ __('site.properties.empty_search') }}</p>
                @endforelse
            </div>

            <div style="margin-top: 24px;">
                {{ $properties->links() }}
            </div>
        </div>
    </section>
</x-layouts.site>
