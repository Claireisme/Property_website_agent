@php
    $agencyName = $agency->name ?? 'Estate Agents Main';
    $county = $agency->county ?? 'Dublin';
    $team = [
        [
            'name' => 'Patrick Doyle',
            'role' => 'Managing Director',
            'image' => 'patrick-doyle.jpg',
            'bio' => 'Leads agency strategy, vendor relationships, and complex private treaty campaigns.',
        ],
        [
            'name' => 'Aoife Byrne',
            'role' => 'Head of Residential Sales',
            'image' => 'aoife-byrne.jpg',
            'bio' => 'Guides sellers from valuation to closing with a calm, data-led sales process.',
        ],
        [
            'name' => 'Maeve O\'Connell',
            'role' => 'Senior Valuer',
            'image' => 'maeve-oconnell.jpg',
            'bio' => 'Specialises in market appraisals, pricing strategy, and pre-sale presentation.',
        ],
        [
            'name' => 'Sophie Reilly',
            'role' => 'Buyer Consultant',
            'image' => 'sophie-reilly.jpg',
            'bio' => 'Helps buyers understand listings, viewings, finance readiness, and next steps.',
        ],
        [
            'name' => 'Niamh Kelly',
            'role' => 'Lettings Specialist',
            'image' => 'niamh-kelly.jpg',
            'bio' => 'Manages rental listings, viewings, tenant checks, and landlord communication.',
        ],
        [
            'name' => 'James Murphy',
            'role' => 'New Homes Advisor',
            'image' => 'james-murphy.jpg',
            'bio' => 'Supports developers and buyers through launches, reservations, and handovers.',
        ],
        [
            'name' => 'Daniel Ryan',
            'role' => 'Property Negotiator',
            'image' => 'daniel-ryan.jpg',
            'bio' => 'Coordinates offers, feedback, and buyer follow-up across active sales campaigns.',
        ],
        [
            'name' => 'Fiona Murray',
            'role' => 'Client Services Lead',
            'image' => 'fiona-murray.jpg',
            'bio' => 'Keeps vendors, landlords, and buyers informed at every important milestone.',
        ],
        [
            'name' => 'Ciaran Walsh',
            'role' => 'Commercial Property Advisor',
            'image' => 'ciaran-walsh.jpg',
            'bio' => 'Advises on offices, retail, industrial space, and mixed-use property opportunities.',
        ],
        [
            'name' => 'Mark Gallagher',
            'role' => 'Operations Manager',
            'image' => 'mark-gallagher.jpg',
            'bio' => 'Oversees compliance, digital workflows, listing quality, and internal systems.',
        ],
    ];
@endphp

<x-layouts.site :agency="$agency" :title="__('site.nav.about')">
    <section class="about-hero">
        <div class="shell about-hero-grid">
            <div>
                <span class="about-kicker">{{ __('site.nav.about') }}</span>
                <h1>{{ $agencyName }}</h1>
                <p class="about-hero-copy">
                    A modern estate agency team serving {{ $county }} and surrounding communities with clear advice, thoughtful marketing, and careful follow-through from first conversation to completion.
                </p>
                <div class="about-actions">
                    <a class="button" href="{{ \App\Support\LocaleUrl::route('properties.index') }}">{{ __('site.actions.view_properties') }}</a>
                    <a class="button secondary" href="{{ \App\Support\LocaleUrl::route('valuation') }}">{{ __('site.actions.request_valuation') }}</a>
                </div>
            </div>
            <div class="about-portrait-panel">
                <img src="{{ asset('images/team/patrick-doyle.jpg') }}" alt="Patrick Doyle, Managing Director" loading="eager">
                <div class="about-portrait-caption">
                    <strong>Local knowledge, properly managed</strong>
                    <span>Sales, lettings, valuations, buyer qualification, and offer support under one team.</span>
                </div>
            </div>
        </div>
    </section>

    <section class="band">
        <div class="shell">
            <div class="about-stats">
                <div class="about-stat">
                    <strong>10</strong>
                    <span>specialist team members</span>
                </div>
                <div class="about-stat">
                    <strong>4</strong>
                    <span>core property services</span>
                </div>
                <div class="about-stat">
                    <strong>7</strong>
                    <span>days enquiry coverage</span>
                </div>
                <div class="about-stat">
                    <strong>1</strong>
                    <span>connected agency workflow</span>
                </div>
            </div>
        </div>
    </section>

    <section class="band" style="padding-top: 0;">
        <div class="shell about-story">
            <div>
                <span class="about-section-label">Company profile</span>
                <h2>Property advice with detail, pace, and accountability.</h2>
            </div>
            <div class="about-copy">
                <p>
                    {{ $agencyName }} is built around a simple promise: every client should understand what is happening, why it matters, and what happens next. We combine local market knowledge with structured digital workflows so listings, enquiries, viewings, valuations, and offers stay organised.
                </p>
                <p>
                    Our work covers residential sales, lettings, commercial property, valuations, and buyer support. Whether a client is preparing a family home for market, comparing rental options, or assessing an investment property, the team keeps the process practical and transparent.
                </p>
                <div class="about-principles">
                    <div class="about-principle">
                        <strong>Clear valuation</strong>
                        <span>Pricing is grounded in local evidence, property condition, demand, and comparable activity.</span>
                    </div>
                    <div class="about-principle">
                        <strong>Better presentation</strong>
                        <span>Listings are prepared for scanning, enquiry conversion, and confident buyer review.</span>
                    </div>
                    <div class="about-principle">
                        <strong>Careful progress</strong>
                        <span>From offer handling to documents, every step is tracked and communicated.</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="band team-section">
        <div class="shell">
            <div class="team-head">
                <div>
                    <span class="about-section-label">Our team</span>
                    <h2>Meet the people behind the listings.</h2>
                </div>
                <p>
                    A balanced team of valuers, negotiators, buyer consultants, lettings specialists, and operations support gives each instruction the right kind of attention.
                </p>
            </div>

            <div class="team-grid">
                @foreach ($team as $member)
                    <article class="team-card">
                        <div class="team-photo">
                            <img src="{{ asset('images/team/'.$member['image']) }}" alt="{{ $member['name'] }}, {{ $member['role'] }}" loading="lazy">
                        </div>
                        <div class="team-body">
                            <h3>{{ $member['name'] }}</h3>
                            <span class="team-role">{{ $member['role'] }}</span>
                            <p>{{ $member['bio'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.site>
