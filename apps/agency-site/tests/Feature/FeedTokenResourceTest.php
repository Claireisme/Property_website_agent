<?php

namespace Tests\Feature;

use App\Filament\Resources\FeedTokens\FeedTokenResource;
use App\Filament\Resources\FeedTokens\Pages\ListFeedTokens;
use Tests\TestCase;

class FeedTokenResourceTest extends TestCase
{
    public function test_feed_token_admin_resource_only_exposes_list_and_view_pages(): void
    {
        $this->assertSame(['index', 'view'], array_keys(FeedTokenResource::getPages()));
    }

    public function test_feed_token_list_page_recommends_keeping_the_token_active(): void
    {
        $subheading = (string) (new ListFeedTokens)->getSubheading();

        $this->assertStringContainsString('Recommended: keep this token active.', $subheading);
        $this->assertStringContainsString('Turning it off will pause the feed', $subheading);
    }
}
