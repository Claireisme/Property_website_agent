@php
    $agency = \App\Models\Agency::query()->first();
    $filtersOpen = request()->except(['category', 'page']) !== [];
    $locationOptions = \App\Support\LocationOptions::searchLocations();
    $selectedLocation = collect($locationOptions)->firstWhere('key', request('region'));
@endphp

<x-layouts.site :agency="$agency" :title="__('site.properties.title')">
    <section class="band">
        <div class="shell">
            <h1 style="font-size: 42px;">{{ __('site.properties.title') }}</h1>
            <p class="muted">{{ __('site.properties.browse_agency', ['agency' => $agency->name ?? 'this agency']) }}</p>

            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 24px;">
                @foreach (\App\Support\PropertyOptions::listingCategories() as $key => $label)
                    <a
                        class="button {{ $category === $key ? '' : 'secondary' }}"
                        href="{{ \App\Support\LocaleUrl::route('properties.index', $key === 'all' ? [] : ['category' => $key]) }}"
                        style="{{ $category === $key ? '' : 'background: #eef2f7; color: var(--ink);' }}"
                    >
                        {{ __('site.properties.categories.'.$key) }}
                    </a>
                @endforeach
            </div>

            <details class="filters-shell" {{ $filtersOpen ? 'open' : '' }}>
                <summary class="filters-summary">
                    <span>Search and advanced filters</span>
                    <span class="muted">{{ $filtersOpen ? 'Filters active' : 'Price, beds, BER, facilities, area and more' }}</span>
                </summary>

                <form class="filters-panel" method="GET" action="{{ \App\Support\LocaleUrl::route('properties.index') }}">
                    <input type="hidden" name="category" value="{{ $category }}">

                    <div class="filters-grid">
                        <label>{{ __('site.properties.search_short_placeholder') }}
                            <input name="q" value="{{ request('q') }}" placeholder="{{ __('site.properties.search_short_placeholder') }}">
                        </label>
                        <label>Region
                            <span class="location-combobox" data-location-combobox>
                                <input
                                    data-location-input
                                    autocomplete="off"
                                    placeholder="Type to filter"
                                    value="{{ $selectedLocation['display'] ?? '' }}"
                                >
                                <input type="hidden" name="region" data-location-value value="{{ request('region') }}">
                                <span class="location-menu" data-location-menu>
                                    <button type="button" class="location-option" data-location-option data-key="" data-search="any region ireland">Any region</button>
                                    @foreach ($locationOptions as $location)
                                        <button
                                            type="button"
                                            class="location-option {{ $location['depth'] ? 'is-child' : '' }}"
                                            data-location-option
                                            data-key="{{ $location['key'] }}"
                                            data-label="{{ $location['display'] }}"
                                            data-search="{{ $location['search'] }}"
                                        >
                                            {{ $location['label'] }}
                                        </button>
                                    @endforeach
                                </span>
                            </span>
                        </label>
                        <label>{{ __('site.labels.property_type') }}
                            <select name="property_type">
                                <option value="">{{ __('site.properties.all_types') }}</option>
                                @foreach (\App\Support\PropertyOptions::propertyTypes() as $key => $label)
                                    <option value="{{ $key }}" @selected(request('property_type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Status
                            <select name="status">
                                <option value="">Any status</option>
                                @foreach (\App\Support\PropertyOptions::publicStatuses() as $key => $label)
                                    <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Min price
                            <input name="min_price" type="number" min="0" step="1000" value="{{ request('min_price') }}">
                        </label>
                        <label>Max price
                            <input name="max_price" type="number" min="0" step="1000" value="{{ request('max_price') }}">
                        </label>
                        <label>Min beds
                            <select name="min_beds">
                                <option value="">Any</option>
                                @foreach ([1, 2, 3, 4, 5] as $bed)
                                    <option value="{{ $bed }}" @selected((string) request('min_beds') === (string) $bed)>{{ $bed }}+</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Min baths
                            <select name="min_baths">
                                <option value="">Any</option>
                                @foreach ([1, 2, 3, 4] as $bath)
                                    <option value="{{ $bath }}" @selected((string) request('min_baths') === (string) $bath)>{{ $bath }}+</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Min SQM
                            <input name="min_area" type="number" min="0" value="{{ request('min_area') }}">
                        </label>
                        <label>Max SQM
                            <input name="max_area" type="number" min="0" value="{{ request('max_area') }}">
                        </label>
                        <label>BER Rating
                            <select name="min_ber">
                                <option value="">Any BER</option>
                                @foreach (\App\Support\PropertyOptions::minimumBerRatings() as $key => $label)
                                    <option value="{{ $key }}" @selected(request('min_ber') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>Sort
                            <select name="sort">
                                @foreach (\App\Support\PropertyOptions::sortOptions() as $key => $label)
                                    <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="checkbox-row">
                        @foreach (\App\Support\PropertyOptions::facilityFilters() as $key => $label)
                            <label>
                                <input name="facilities[]" type="checkbox" value="{{ $key }}" @checked(in_array($key, (array) request('facilities', []), true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>

                    <div class="filters-actions">
                        <button type="submit">{{ __('site.actions.search') }}</button>
                        <a class="muted" href="{{ \App\Support\LocaleUrl::route('properties.index', $category === 'all' ? [] : ['category' => $category]) }}">Clear filters</a>
                    </div>
                </form>
            </details>

            <script>
                (() => {
                    const combo = document.querySelector('[data-location-combobox]');
                    if (! combo) return;

                    const form = combo.closest('form');
                    const input = combo.querySelector('[data-location-input]');
                    const value = combo.querySelector('[data-location-value]');
                    const options = Array.from(combo.querySelectorAll('[data-location-option]'));

                    const open = () => combo.classList.add('is-open');
                    const close = () => combo.classList.remove('is-open');
                    const normalize = (text) => text.toLowerCase().trim();

                    const filter = () => {
                        const term = normalize(input.value);
                        let visible = 0;

                        options.forEach((option) => {
                            const matches = ! term || option.dataset.search.includes(term);
                            option.hidden = ! matches;
                            if (matches) visible += 1;
                        });

                        const exact = options.find((option) => {
                            return option.dataset.key && normalize(option.dataset.label || option.textContent) === term;
                        });

                        if (exact) {
                            value.value = exact.dataset.key;
                        } else if (term === '') {
                            value.value = '';
                        }

                        combo.classList.toggle('is-empty', visible === 0);
                    };

                    const selectFirstVisible = () => {
                        if (value.value || ! input.value.trim()) return;

                        const first = options.find((option) => ! option.hidden && option.dataset.key);

                        if (first) {
                            value.value = first.dataset.key;
                            input.value = first.dataset.label;
                        }
                    };

                    input.addEventListener('focus', () => {
                        open();
                        filter();
                    });

                    input.addEventListener('input', () => {
                        open();
                        value.value = '';
                        filter();
                    });

                    options.forEach((option) => {
                        option.addEventListener('click', () => {
                            value.value = option.dataset.key || '';
                            input.value = option.dataset.label || '';
                            close();
                        });
                    });

                    document.addEventListener('click', (event) => {
                        if (! combo.contains(event.target)) {
                            close();
                        }
                    });

                    form?.addEventListener('submit', () => {
                        filter();
                        selectFirstVisible();
                    });
                })();
            </script>

            <div class="grid" style="margin-top: 26px;">
                @forelse ($properties as $property)
                    @include('properties.partials.card', ['property' => $property])
                @empty
                    <p class="muted">{{ __('site.home.empty_properties') }}</p>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.site>
