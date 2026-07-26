<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Enquiry;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class TeamMemberAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_accounts_can_manage_listings_and_enquiries_but_not_delete(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $agent = User::factory()->create(['role' => 'agent']);
        TeamMember::query()->create([
            'agency_id' => $agency->id,
            'user_id' => $agent->id,
            'name' => 'Agent One',
            'email' => $agent->email,
            'is_active' => true,
        ]);
        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Permission Property',
            'slug' => 'permission-property',
            'public_id' => 'prop_permission',
            'status' => 'available',
        ]);
        $enquiry = Enquiry::query()->create([
            'property_id' => $property->id,
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'source' => 'agency_site',
            'status' => 'new',
        ]);

        $this->assertTrue(Gate::forUser($agent)->allows('viewAny', Property::class));
        $this->assertTrue(Gate::forUser($agent)->allows('update', $property));
        $this->assertTrue(Gate::forUser($agent)->allows('viewAny', Enquiry::class));
        $this->assertTrue(Gate::forUser($agent)->allows('update', $enquiry));
        $this->assertFalse(Gate::forUser($agent)->allows('delete', $property));
        $this->assertFalse(Gate::forUser($agent)->allows('delete', $enquiry));
        $this->assertFalse(Gate::forUser($agent)->allows('viewAny', TeamMember::class));

        $this->assertTrue(Gate::forUser($admin)->allows('delete', $property));
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $enquiry));
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', TeamMember::class));
    }

    public function test_property_created_by_agent_account_is_assigned_to_that_team_member(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);
        $agent = User::factory()->create(['role' => 'agent']);
        $teamMember = TeamMember::query()->create([
            'agency_id' => $agency->id,
            'user_id' => $agent->id,
            'name' => 'Agent One',
            'email' => $agent->email,
            'is_active' => true,
        ]);

        $this->actingAs($agent);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Agent Created Property',
            'slug' => 'agent-created-property',
            'public_id' => 'prop_agent_created',
            'status' => 'available',
        ]);

        $this->assertSame($teamMember->id, $property->team_member_id);
    }

    public function test_property_enquiry_card_uses_agent_photo_before_agency_logo(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
            'logo_path' => 'agency/logos/fallback-logo.png',
        ]);
        $teamMember = TeamMember::query()->create([
            'agency_id' => $agency->id,
            'name' => 'Agent One',
            'role' => 'Senior Negotiator',
            'email' => 'agent@example.com',
            'photo_path' => 'team/agent-one.jpg',
            'is_active' => true,
        ]);
        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'team_member_id' => $teamMember->id,
            'title' => 'Avatar Property',
            'slug' => 'avatar-property',
            'public_id' => 'prop_avatar',
            'status' => 'available',
        ]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee('/storage/team/agent-one.jpg', false)
            ->assertSee('Agent One profile photo')
            ->assertSee('Send Agent One a message or request a viewing')
            ->assertSee('Senior Negotiator')
            ->assertDontSee('/storage/agency/logos/fallback-logo.png', false);
    }

    public function test_property_enquiry_card_falls_back_to_agency_logo_when_agent_has_no_photo(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
            'logo_path' => 'agency/logos/fallback-logo.png',
        ]);
        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Logo Fallback Property',
            'slug' => 'logo-fallback-property',
            'public_id' => 'prop_logo_fallback',
            'status' => 'available',
        ]);

        $this->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee('/storage/agency/logos/fallback-logo.png', false)
            ->assertSee('Test Agency logo')
            ->assertSee('Send Test Agency a message or request a viewing');
    }

    public function test_agent_can_manage_property_images_without_delete_permission(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);
        $agent = User::factory()->create(['role' => 'agent']);
        TeamMember::query()->create([
            'agency_id' => $agency->id,
            'user_id' => $agent->id,
            'name' => 'Agent One',
            'email' => $agent->email,
            'is_active' => true,
        ]);
        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Image Permission Property',
            'slug' => 'image-permission-property',
            'public_id' => 'prop_image_permission',
            'status' => 'available',
        ]);
        $image = PropertyImage::withoutEvents(fn () => PropertyImage::query()->create([
            'property_id' => $property->id,
            'original_url' => 'properties/test/image.jpg',
        ]));

        $this->assertTrue(Gate::forUser($agent)->allows('update', $image));
        $this->assertFalse(Gate::forUser($agent)->allows('delete', $image));
    }
}
