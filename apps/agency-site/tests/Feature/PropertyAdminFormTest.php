<?php

namespace Tests\Feature;

use App\Filament\Resources\Properties\PropertyResource;
use App\Models\Agency;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyAdminFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_property_form_shows_existing_photos_for_reordering_and_caption_updates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);
        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Editable Image Property',
            'slug' => 'editable-image-property',
            'public_id' => 'prop_edit_images',
            'status' => 'available',
        ]);

        PropertyImage::withoutEvents(fn () => PropertyImage::query()->create([
            'property_id' => $property->id,
            'original_url' => 'properties/originals/front.jpg',
            'caption' => 'Front exterior',
            'sort_order' => 1,
        ]));

        $this->actingAs($admin)
            ->get(PropertyResource::getUrl('edit', ['record' => $property], isAbsolute: false))
            ->assertOk()
            ->assertSee('Uploaded photos')
            ->assertSee('Front exterior')
            ->assertSee('Drag photos or use the move buttons')
            ->assertSee('property-photo-repeater', false)
            ->assertSee('property-existing-photo-preview', false)
            ->assertSee('Property photo preview', false);
    }

    public function test_edit_property_form_treats_public_id_and_slug_as_fixed_identifiers(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);
        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Stable Identifier Property',
            'slug' => 'stable-identifier-property',
            'public_id' => 'prop_stable_identifier',
            'status' => 'available',
        ]);

        $this->actingAs($admin)
            ->get(PropertyResource::getUrl('edit', ['record' => $property], isAbsolute: false))
            ->assertOk()
            ->assertSee('Generated when the property was created and kept fixed for feeds, links, and integrations.')
            ->assertSee('Generated when the property was created and kept fixed so existing property links remain stable.');

        $property->update([
            'title' => 'Updated Property Details',
            'public_id' => 'prop_changed_identifier',
            'slug' => 'changed-property-link',
        ]);

        $property->refresh();

        $this->assertSame('Updated Property Details', $property->title);
        $this->assertSame('prop_stable_identifier', $property->public_id);
        $this->assertSame('stable-identifier-property', $property->slug);
    }

    public function test_create_property_form_keeps_historical_photo_manager_hidden(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(PropertyResource::getUrl('create', isAbsolute: false))
            ->assertOk()
            ->assertSee('Bulk upload photos')
            ->assertDontSee('Uploaded photos');
    }
}
