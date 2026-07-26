<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\Enquiry;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyEnquiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_enquiry_form_stores_message_and_type_for_admin_review(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Enquiry Property',
            'slug' => 'enquiry-property',
            'public_id' => 'prop_enquiry',
            'status' => 'available',
            'published_at' => now(),
        ]);

        $this->from(route('properties.show', $property))
            ->post(route('properties.enquiries.store', $property), [
                'name' => 'Buyer One',
                'email' => 'buyer@example.com',
                'phone' => '0871234567',
                'enquiry_type' => 'viewing',
                'message' => 'Could I arrange a viewing this week?',
            ])->assertRedirect(route('properties.show', $property).'#property-enquiry')
            ->assertSessionHas('status', __('site.messages.enquiry_sent'));

        $this->assertDatabaseHas(Enquiry::class, [
            'property_id' => $property->id,
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'phone' => '0871234567',
            'enquiry_type' => 'viewing',
            'message' => 'Could I arrange a viewing this week?',
            'source' => 'agency_site',
            'status' => 'new',
        ]);

        $this->withSession(['status' => __('site.messages.enquiry_sent')])
            ->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee(__('site.messages.enquiry_sent'))
            ->assertSee('The agency will review your message and contact you soon.');
    }
}
