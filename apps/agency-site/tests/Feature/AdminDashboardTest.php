<?php

namespace Tests\Feature;

use App\Models\Agency;
use App\Models\BuyerAccessRequest;
use App\Models\Enquiry;
use App\Models\Offer;
use App\Models\Property;
use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_actionable_workflow_queues(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createProperty();

        Enquiry::query()->create([
            'property_id' => $property->id,
            'name' => 'Mary Enquirer',
            'email' => 'mary@example.test',
            'enquiry_type' => 'viewing',
            'message' => 'Can I view this property?',
            'status' => 'new',
        ]);

        BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Sean Buyer',
            'buyer_email' => 'sean@example.test',
            'status' => 'pending_review',
            'requested_at' => now(),
        ]);

        Offer::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Aisling Bidder',
            'buyer_email' => 'aisling@example.test',
            'amount' => 455000,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        ValuationRequest::query()->create([
            'name' => 'Liam Seller',
            'email' => 'liam@example.test',
            'property_address' => '10 Test Road, Dublin',
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Today at a glance')
            ->assertSee('Latest action queue')
            ->assertSee('Mary Enquirer')
            ->assertSee('Sean Buyer')
            ->assertSee('Aisling Bidder')
            ->assertSee('Liam Seller')
            ->assertDontSee('Documentation')
            ->assertDontSee('GitHub');
    }

    public function test_admin_dashboard_hides_empty_action_queue_modules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $property = $this->createProperty();

        Enquiry::query()->create([
            'property_id' => $property->id,
            'name' => 'Queue Only Enquirer',
            'email' => 'queue@example.test',
            'enquiry_type' => 'question',
            'message' => 'Is this still available?',
            'status' => 'new',
        ]);

        BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Approved Buyer',
            'buyer_email' => 'approved@example.test',
            'status' => 'approved',
            'requested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Only queues with items requiring action are shown here.')
            ->assertSee('Recent enquiries')
            ->assertSee('Queue Only Enquirer')
            ->assertDontSee('Registration and document checks.')
            ->assertDontSee('Review access')
            ->assertDontSee('Buyer bids and follow-up items.')
            ->assertDontSee('Open offers')
            ->assertDontSee('New seller leads from the site.')
            ->assertDontSee('Open valuations');
    }

    private function createProperty(): Property
    {
        $agency = Agency::query()->create([
            'name' => 'Dashboard Agency',
        ]);

        return Property::query()->create([
            'agency_id' => $agency->id,
            'public_id' => 'prop_dashboard_queue',
            'title' => 'Dashboard Test Property',
            'slug' => 'dashboard-test-property',
            'status' => 'available',
            'transaction_type' => 'sale',
            'property_type' => 'terraced',
            'price' => 455000,
        ]);
    }
}
