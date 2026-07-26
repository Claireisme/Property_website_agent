<x-layouts.site :agency="$agency" :title="__('site.nav.contact')">
    <section class="band">
        <div class="shell" style="max-width: 760px;">
            <h1 style="font-size: 42px;">{{ __('site.nav.contact') }} {{ $agency->name ?? 'the agency' }}</h1>
            <p class="muted">{{ $agency->address ?? '' }} {{ $agency->county ?? '' }}</p>
            @if (session('status'))
                <div class="notice">{{ session('status') }}</div>
            @endif
            <form class="form" method="POST" action="{{ \App\Support\LocaleUrl::route('contact.store') }}">
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
                <label class="span-2">{{ __('site.labels.message') }}
                    <textarea name="message" rows="6">{{ old('message') }}</textarea>
                </label>
                <button type="submit">{{ __('site.actions.send_message') }}</button>
            </form>
        </div>
    </section>
</x-layouts.site>
