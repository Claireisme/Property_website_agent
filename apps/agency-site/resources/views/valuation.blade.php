@php
    $agency = \App\Models\Agency::query()->first();
@endphp

<x-layouts.site :agency="$agency" :title="__('site.nav.valuation')">
    <section class="band">
        <div class="shell" style="max-width: 820px;">
            <h1 style="font-size: 42px;">{{ __('site.actions.request_valuation') }}</h1>
            <p class="muted">{{ __('site.valuation_intro') }}</p>
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            <form class="form" method="POST" action="{{ \App\Support\LocaleUrl::route('valuation.store') }}">
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
                <label>{{ __('site.labels.preferred_contact') }}
                    <select name="preferred_contact_method">
                        <option value="">{{ __('site.labels.select') }}</option>
                        <option value="email">{{ __('site.labels.email') }}</option>
                        <option value="phone">{{ __('site.labels.phone') }}</option>
                        <option value="either">{{ __('site.labels.either') }}</option>
                    </select>
                </label>
                <label class="span-2">{{ __('site.labels.property_address') }}
                    <input name="property_address" value="{{ old('property_address') }}" required>
                </label>
                <label>{{ __('site.labels.eircode') }}
                    <input name="eircode" value="{{ old('eircode') }}">
                </label>
                <label>{{ __('site.labels.property_type') }}
                    <select name="property_type">
                        <option value="">{{ __('site.labels.select') }}</option>
                        <option value="house">{{ __('site.types.house') }}</option>
                        <option value="apartment">{{ __('site.types.apartment') }}</option>
                        <option value="bungalow">{{ __('site.types.bungalow') }}</option>
                        <option value="terraced">{{ __('site.types.terraced') }}</option>
                        <option value="semi_detached">{{ __('site.types.semi_detached') }}</option>
                        <option value="detached">{{ __('site.types.detached') }}</option>
                    </select>
                </label>
                <label>{{ __('site.labels.bedrooms_field') }}
                    <input name="bedrooms" type="number" min="0" value="{{ old('bedrooms') }}">
                </label>
                <label>{{ __('site.labels.bathrooms_field') }}
                    <input name="bathrooms" type="number" min="0" value="{{ old('bathrooms') }}">
                </label>
                <label>{{ __('site.labels.selling_timeline') }}
                    <select name="selling_timeline">
                        <option value="">{{ __('site.labels.select') }}</option>
                        <option value="asap">{{ __('site.labels.asap') }}</option>
                        <option value="1_3_months">{{ __('site.labels.1_3_months') }}</option>
                        <option value="3_6_months">{{ __('site.labels.3_6_months') }}</option>
                        <option value="6_plus_months">{{ __('site.labels.6_plus_months') }}</option>
                        <option value="just_researching">{{ __('site.labels.just_researching') }}</option>
                    </select>
                </label>
                <label class="span-2">{{ __('site.labels.message') }}
                    <textarea name="message" rows="6">{{ old('message') }}</textarea>
                </label>
                <button type="submit">{{ __('site.actions.send_valuation_request') }}</button>
            </form>
        </div>
    </section>
</x-layouts.site>
