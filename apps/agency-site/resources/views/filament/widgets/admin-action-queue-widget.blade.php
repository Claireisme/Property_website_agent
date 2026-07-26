<x-filament-widgets::widget>
    <x-filament::section
        heading="Latest action queue"
        description="Only queues with items requiring action are shown here."
    >
        @if ($cards === [])
            <div class="admin-action-empty">
                No workflow items need attention right now.
            </div>
        @else
            <div class="admin-action-grid">
                @foreach ($cards as $card)
                    <section class="admin-action-card">
                        <div class="admin-action-card-header">
                            <div>
                                <h3 class="admin-action-card-title">
                                    {{ $card['title'] }}
                                </h3>
                                <p class="admin-action-card-description">
                                    {{ $card['description'] }}
                                </p>
                            </div>

                            <span class="admin-action-count">
                                {{ $card['count'] }}
                            </span>
                        </div>

                        <div class="admin-action-list">
                            @forelse ($card['items'] as $item)
                                <a
                                    href="{{ $item['url'] }}"
                                    class="admin-action-item"
                                >
                                    <div class="admin-action-item-header">
                                        <div>
                                            <p class="admin-action-person">
                                                {{ $item['label'] }}
                                            </p>
                                            <p class="admin-action-property">
                                                {{ $item['title'] }}
                                            </p>
                                        </div>

                                        <x-filament::badge :color="$item['color']">
                                            {{ $item['badge'] }}
                                        </x-filament::badge>
                                    </div>

                                    <div class="admin-action-meta">
                                        <span>{{ $item['meta'] }}</span>

                                        @isset($item['amount'])
                                            <span class="admin-action-amount">
                                                {{ $item['amount'] }}
                                            </span>
                                        @endisset
                                    </div>
                                </a>
                            @empty
                                <div class="admin-action-empty">
                                    {{ $card['empty'] }}
                                </div>
                            @endforelse

                            @if ($card['count'] > count($card['items']))
                                <p class="admin-action-more">
                                    {{ $card['count'] - count($card['items']) }} more waiting in this queue
                                </p>
                            @endif
                        </div>

                        <a
                            href="{{ $card['url'] }}"
                            class="admin-action-link"
                        >
                            {{ $card['action'] }}
                        </a>
                    </section>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
