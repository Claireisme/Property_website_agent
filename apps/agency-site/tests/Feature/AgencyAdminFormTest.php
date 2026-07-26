<?php

namespace Tests\Feature;

use App\Filament\Resources\Agencies\AgencyResource;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgencyAdminFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_form_includes_configurable_bid_increment_ranges(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $this->actingAs($admin)
            ->get(AgencyResource::getUrl('edit', ['record' => $agency], isAbsolute: false))
            ->assertOk()
            ->assertSee('Online bidding increments')
            ->assertSee('Bid increment ranges')
            ->assertSee('Add price range');
    }

    public function test_site_watermark_can_be_enabled_from_configuration(): void
    {
        config([
            'app.watermark.enabled' => true,
            'app.watermark.text' => 'Demo website',
        ]);

        Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('<div class="site-watermark" aria-hidden="true">', false)
            ->assertSee('Demo website');
    }

    public function test_site_watermark_can_be_disabled_from_configuration(): void
    {
        config([
            'app.watermark.enabled' => false,
            'app.watermark.text' => 'Demo website',
        ]);

        Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('<div class="site-watermark" aria-hidden="true">', false);
    }
}
