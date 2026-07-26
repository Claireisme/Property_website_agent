<?php

namespace App\Filament\Resources\FeedTokens\Pages;

use App\Filament\Resources\FeedTokens\FeedTokenResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ListFeedTokens extends ListRecords
{
    protected static string $resource = FeedTokenResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(<<<'HTML'
            <span style="display: inline-flex; align-items: flex-start; gap: 12px; max-width: 920px; margin-top: 10px; padding: 14px 16px; border: 1px solid #99f6e4; border-left: 5px solid #0f766e; border-radius: 14px; background: linear-gradient(135deg, #ecfdf5 0%, #f8fafc 100%); color: #0f172a; box-shadow: 0 14px 28px rgba(15, 118, 110, 0.10);">
                <span style="display: inline-flex; align-items: center; justify-content: center; flex: 0 0 28px; width: 28px; height: 28px; border-radius: 999px; background: #0f766e; color: #ffffff; font-size: 16px; font-weight: 800; line-height: 1;">!</span>
                <span style="display: grid; gap: 3px;">
                    <strong style="font-size: 15px; font-weight: 800; color: #0f172a;">Recommended: keep this token active.</strong>
                    <span style="font-size: 14px; line-height: 1.55; color: #475569;">The main portal uses this token to sync property data. Turning it off will pause the feed until it is enabled again.</span>
                </span>
            </span>
        HTML);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
