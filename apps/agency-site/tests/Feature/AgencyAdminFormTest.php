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
}
