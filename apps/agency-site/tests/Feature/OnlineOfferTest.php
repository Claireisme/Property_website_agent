<?php

namespace Tests\Feature;

use App\Filament\Resources\BuyerAccessRequests\BuyerAccessRequestResource;
use App\Mail\TemplatedEmail;
use App\Models\Agency;
use App\Models\BuyerAccessRequest;
use App\Models\EmailDeliveryLog;
use App\Models\Offer;
use App\Models\OfferEvent;
use App\Models\Property;
use App\Models\User;
use App\Services\BuyerEmailVerificationService;
use App\Support\BidIncrementRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OnlineOfferTest extends TestCase
{
    use RefreshDatabase;

    public function test_bid_increment_rules_calculate_next_valid_offer_amount(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
            'bid_increment_rules' => [
                ['min_price' => 0, 'max_price' => 300000, 'increment_amount' => 500],
                ['min_price' => 300001, 'max_price' => 1000000, 'increment_amount' => 1000],
                ['min_price' => 1000001, 'max_price' => null, 'increment_amount' => 5000],
            ],
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Rule Checked Property',
            'slug' => 'rule-checked-property',
            'public_id' => 'prop_rule_checked',
            'status' => 'available',
            'price' => 500000,
            'online_offers_enabled' => true,
        ]);

        $this->assertSame(1000, BidIncrementRules::incrementForProperty($property));
        $this->assertSame(500000, BidIncrementRules::nextOfferAmount($property));
        $this->assertNull(BidIncrementRules::amountValidationMessage($property, 500000));
        $this->assertNotNull(BidIncrementRules::amountValidationMessage($property, 500500));

        Offer::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'amount' => 520000,
            'status' => 'pending_review',
            'consent_to_terms' => true,
            'submitted_at' => now(),
        ]);

        $this->assertSame(520000, BidIncrementRules::currentBaseAmount($property));
        $this->assertSame(521000, BidIncrementRules::nextOfferAmount($property));
        $this->assertNull(BidIncrementRules::amountValidationMessage($property, 521000));
        $this->assertNotNull(BidIncrementRules::amountValidationMessage($property, 521500));
    }

    public function test_buyer_can_request_bidding_access_for_enabled_property(): void
    {
        Storage::fake('local');

        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Access Enabled Property',
            'slug' => 'access-enabled-property',
            'public_id' => 'prop_access_enabled',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        $verificationCode = app(BuyerEmailVerificationService::class)->issue('buyer@example.com');

        $this->post(route('buyer.register'), [
            'buyer_register_email' => 'buyer@example.com',
            'buyer_register_password' => 'secure-password',
            'buyer_register_verification_code' => $verificationCode,
        ])->assertRedirect();

        $buyer = User::query()->where('email', 'buyer@example.com')->firstOrFail();

        $this->post(route('properties.buyer-access-requests.store', $property), [
            'buyer_name' => 'Buyer One',
            'buyer_phone' => '0871234567',
            'initial_offer_amount' => 475000,
            'buyer_position' => 'first_time_buyer',
            'financing_type' => 'mortgage',
            'mortgage_approval_status' => 'approved_in_principle',
            'proof_of_funds_document' => UploadedFile::fake()->create('approval.pdf', 100, 'application/pdf'),
            'identity_document' => UploadedFile::fake()->image('passport.jpg'),
            'consent_to_terms' => '1',
        ])->assertRedirect();

        $accessRequest = BuyerAccessRequest::query()->firstOrFail();

        $this->assertSame('buyer', $buyer->role);
        $this->assertSame($buyer->id, $accessRequest->user_id);
        $this->assertSame('pending_review', $accessRequest->status);
        $this->assertSame(475000, $accessRequest->initial_offer_amount);
        $this->assertNotNull($accessRequest->proof_of_funds_path);
        $this->assertNotNull($accessRequest->identity_document_path);
        $this->assertAuthenticatedAs($buyer);

        Storage::disk('local')->assertExists($accessRequest->proof_of_funds_path);
        Storage::disk('local')->assertExists($accessRequest->identity_document_path);
    }

    public function test_buyer_access_initial_offer_must_follow_increment_rule(): void
    {
        Storage::fake('local');

        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Increment Protected Property',
            'slug' => 'increment-protected-property',
            'public_id' => 'prop_increment_protected',
            'status' => 'available',
            'price' => 500000,
            'online_offers_enabled' => true,
        ]);

        $buyer = User::factory()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);

        $this->actingAs($buyer)->post(route('properties.buyer-access-requests.store', $property), [
            'buyer_name' => 'Buyer One',
            'buyer_phone' => '0871234567',
            'initial_offer_amount' => 500500,
            'buyer_position' => 'first_time_buyer',
            'financing_type' => 'mortgage',
            'mortgage_approval_status' => 'approved_in_principle',
            'proof_of_funds_document' => UploadedFile::fake()->create('approval.pdf', 100, 'application/pdf'),
            'identity_document' => UploadedFile::fake()->image('passport.jpg'),
            'consent_to_terms' => '1',
        ])->assertSessionHasErrors('initial_offer_amount');

        $this->assertDatabaseCount(BuyerAccessRequest::class, 0);
    }

    public function test_property_page_prefills_buyer_access_offer_with_next_valid_amount(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Prefilled Offer Property',
            'slug' => 'prefilled-offer-property',
            'public_id' => 'prop_prefilled_offer',
            'status' => 'available',
            'price' => 500000,
            'online_offers_enabled' => true,
        ]);

        $buyer = User::factory()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);

        $this->actingAs($buyer)
            ->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee('name="initial_offer_amount"', false)
            ->assertSee('min="500000"', false)
            ->assertSee('step="1000"', false)
            ->assertSee('Start from EUR 500,000', false);

        Offer::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Existing Buyer',
            'buyer_email' => 'existing@example.com',
            'amount' => 520000,
            'status' => 'pending_review',
            'consent_to_terms' => true,
            'submitted_at' => now(),
        ]);

        $this->actingAs($buyer)
            ->get(route('properties.show', $property))
            ->assertOk()
            ->assertSee('min="521000"', false)
            ->assertSee('value="521000"', false)
            ->assertSee('Start from EUR 521,000', false);
    }

    public function test_admin_can_preview_and_download_buyer_access_documents(): void
    {
        Storage::fake('local');

        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Document Review Property',
            'slug' => 'document-review-property',
            'public_id' => 'prop_document_review',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        Storage::disk('local')->put('buyer-access/proof-of-funds/approval.pdf', '%PDF-1.4 test');
        Storage::disk('local')->put('buyer-access/identity-documents/passport.jpg', 'fake image');

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'status' => 'pending_review',
            'proof_of_funds_path' => 'buyer-access/proof-of-funds/approval.pdf',
            'identity_document_path' => 'buyer-access/identity-documents/passport.jpg',
            'consent_to_terms' => true,
            'requested_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($admin)
            ->get('/admin/buyer-access-requests/'.$accessRequest->id)
            ->assertOk()
            ->assertSee('Proof of funds or mortgage approval')
            ->assertSee('Photo ID')
            ->assertSee('Approve')
            ->assertSee('Reject');

        $this->actingAs($admin)
            ->get(route('admin.buyer-access-requests.documents.show', [$accessRequest, 'proof']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.buyer-access-requests.documents.download', [$accessRequest, 'identity']))
            ->assertOk();

        $this->actingAs($buyer)
            ->get(route('admin.buyer-access-requests.documents.show', [$accessRequest, 'proof']))
            ->assertForbidden();
    }

    public function test_admin_can_approve_and_reject_buyer_access_request(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Access Review Property',
            'slug' => 'access-review-property',
            'public_id' => 'prop_access_review',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'status' => 'pending_review',
            'consent_to_terms' => true,
            'requested_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.buyer-access-requests.approve', $accessRequest))
            ->assertRedirect();

        $accessRequest->refresh();

        $this->assertSame('approved', $accessRequest->status);
        $this->assertNotNull($accessRequest->approved_at);
        $this->assertNull($accessRequest->rejected_at);

        $this->actingAs($admin)
            ->post(route('admin.buyer-access-requests.reject', $accessRequest))
            ->assertRedirect();

        $accessRequest->refresh();

        $this->assertSame('rejected', $accessRequest->status);
        $this->assertNull($accessRequest->approved_at);
        $this->assertNotNull($accessRequest->rejected_at);
    }

    public function test_admin_buyer_access_view_uses_compact_document_cards_with_review_actions(): void
    {
        Storage::fake('local');

        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Document Review Property',
            'slug' => 'document-review-property',
            'public_id' => 'prop_document_review',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        Storage::disk('local')->put('buyer-access/proof-of-funds/proof.jpg', 'fake image bytes');
        Storage::disk('local')->put('buyer-access/identity-documents/id.pdf', 'fake pdf bytes');

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'status' => 'pending_review',
            'initial_offer_amount' => 475000,
            'proof_of_funds_path' => 'buyer-access/proof-of-funds/proof.jpg',
            'identity_document_path' => 'buyer-access/identity-documents/id.pdf',
            'consent_to_terms' => true,
            'requested_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(BuyerAccessRequestResource::getUrl('view', ['record' => $accessRequest], isAbsolute: false))
            ->assertOk()
            ->assertSeeInOrder(['Approve', 'Reject', 'Proof of funds or mortgage approval', 'Photo ID'])
            ->assertSee('buyer-access-review', false)
            ->assertSee('buyer-access-document__thumb', false)
            ->assertSee('buyer-access-document-modal', false)
            ->assertSee('data-buyer-access-preview-trigger', false)
            ->assertSee('<iframe src=', false)
            ->assertSee('Open preview')
            ->assertSee('Download');
    }

    public function test_approving_buyer_access_creates_offer_from_initial_amount(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Initial Offer Property',
            'slug' => 'initial-offer-property',
            'public_id' => 'prop_initial_offer',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        $buyer = User::factory()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'buyer_phone' => '0871234567',
            'status' => 'pending_review',
            'initial_offer_amount' => 475000,
            'buyer_position' => 'first_time_buyer',
            'financing_type' => 'mortgage',
            'mortgage_approval_status' => 'approved_in_principle',
            'message' => 'Ready to proceed.',
            'consent_to_terms' => true,
            'requested_at' => now(),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.buyer-access-requests.approve', $accessRequest))
            ->assertRedirect();

        $this->assertDatabaseHas(Offer::class, [
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_access_request_id' => $accessRequest->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'amount' => 475000,
            'status' => 'submitted',
            'buyer_position' => 'first_time_buyer',
            'financing_type' => 'mortgage',
            'mortgage_approval_status' => 'approved_in_principle',
            'message' => 'Ready to proceed.',
            'consent_to_terms' => true,
        ]);

        $this->assertDatabaseHas(OfferEvent::class, [
            'event_type' => 'offer_created_from_access_approval',
            'actor_type' => 'agent',
        ]);
    }

    public function test_buyer_can_submit_online_offer_after_access_is_approved(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Offer Enabled Property',
            'slug' => 'offer-enabled-property',
            'public_id' => 'prop_offer_enabled',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        $buyer = User::factory()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'status' => 'approved',
            'consent_to_terms' => true,
            'requested_at' => now(),
            'approved_at' => now(),
        ]);

        $this->actingAs($buyer)->post(route('properties.offers.store', $property), [
            'buyer_access_request_id' => $accessRequest->id,
            'amount' => 475000,
            'financing_type' => 'mortgage',
            'consent_to_terms' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas(Offer::class, [
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_access_request_id' => $accessRequest->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'amount' => 475000,
            'status' => 'submitted',
            'consent_to_terms' => true,
        ]);

        $this->assertDatabaseHas(OfferEvent::class, [
            'event_type' => 'offer_submitted',
            'actor_type' => 'buyer',
        ]);
    }

    public function test_online_offer_must_follow_increment_rule_from_current_highest_offer(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Current Offer Property',
            'slug' => 'current-offer-property',
            'public_id' => 'prop_current_offer',
            'status' => 'available',
            'price' => 500000,
            'online_offers_enabled' => true,
        ]);

        Offer::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Existing Buyer',
            'buyer_email' => 'existing@example.com',
            'amount' => 520000,
            'status' => 'pending_review',
            'consent_to_terms' => true,
            'submitted_at' => now(),
        ]);

        $buyer = User::factory()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'status' => 'approved',
            'consent_to_terms' => true,
            'requested_at' => now(),
            'approved_at' => now(),
        ]);

        $this->actingAs($buyer)->post(route('properties.offers.store', $property), [
            'buyer_access_request_id' => $accessRequest->id,
            'amount' => 521500,
            'financing_type' => 'mortgage',
            'consent_to_terms' => '1',
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount(Offer::class, 1);
    }

    public function test_offer_submission_requires_approved_buyer_access(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Pending Access Property',
            'slug' => 'pending-access-property',
            'public_id' => 'prop_pending_access',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        $buyer = User::factory()->create([
            'name' => 'Buyer One',
            'email' => 'buyer@example.com',
            'role' => 'buyer',
        ]);

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'user_id' => $buyer->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'status' => 'pending_review',
            'consent_to_terms' => true,
            'requested_at' => now(),
        ]);

        $this->actingAs($buyer)->post(route('properties.offers.store', $property), [
            'buyer_access_request_id' => $accessRequest->id,
            'amount' => 475000,
            'consent_to_terms' => '1',
        ])->assertForbidden();

        $this->assertDatabaseMissing(Offer::class, [
            'property_id' => $property->id,
            'buyer_email' => 'buyer@example.com',
        ]);
    }

    public function test_offer_submission_requires_a_signed_in_buyer_account(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Approved Access Property',
            'slug' => 'approved-access-property',
            'public_id' => 'prop_approved_access',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        $accessRequest = BuyerAccessRequest::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'status' => 'approved',
            'consent_to_terms' => true,
            'requested_at' => now(),
            'approved_at' => now(),
        ]);

        $this->post(route('properties.offers.store', $property), [
            'buyer_access_request_id' => $accessRequest->id,
            'amount' => 475000,
            'consent_to_terms' => '1',
        ])->assertForbidden();

        $this->assertDatabaseMissing(Offer::class, [
            'property_id' => $property->id,
            'buyer_access_request_id' => $accessRequest->id,
        ]);
    }

    public function test_offer_submission_is_not_available_when_disabled(): void
    {
        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Offer Disabled Property',
            'slug' => 'offer-disabled-property',
            'public_id' => 'prop_offer_disabled',
            'status' => 'available',
            'online_offers_enabled' => false,
        ]);

        $this->post(route('properties.offers.store', $property), [
            'buyer_access_request_id' => 1,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'amount' => 475000,
            'consent_to_terms' => '1',
        ])->assertNotFound();
    }

    public function test_accepting_offer_marks_property_sale_agreed_and_records_event(): void
    {
        Mail::fake();

        $agency = Agency::query()->create([
            'name' => 'Test Agency',
        ]);

        $property = Property::query()->create([
            'agency_id' => $agency->id,
            'title' => 'Accepted Offer Property',
            'slug' => 'accepted-offer-property',
            'public_id' => 'prop_accepted_offer',
            'status' => 'available',
            'online_offers_enabled' => true,
        ]);

        $offer = Offer::query()->create([
            'property_id' => $property->id,
            'buyer_name' => 'Buyer One',
            'buyer_email' => 'buyer@example.com',
            'amount' => 475000,
            'status' => 'valid',
            'consent_to_terms' => true,
            'submitted_at' => now(),
        ]);

        $offer->update([
            'status' => 'accepted_subject_to_contract',
        ]);

        $this->assertSame('sale_agreed', $property->refresh()->status);
        $this->assertNotNull($property->sale_agreed_at);
        $this->assertNotNull($offer->refresh()->accepted_at);

        $this->assertDatabaseHas(OfferEvent::class, [
            'offer_id' => $offer->id,
            'actor_type' => 'agent',
            'event_type' => 'status_changed',
        ]);

        Mail::assertSent(TemplatedEmail::class, function (TemplatedEmail $mail): bool {
            return str_contains($mail->subjectLine, 'successful')
                && str_contains($mail->textBody, 'Accepted Offer Property');
        });

        $this->assertDatabaseHas(EmailDeliveryLog::class, [
            'template_key' => 'offer_won',
            'recipient_email' => 'buyer@example.com',
            'status' => 'sent',
        ]);
    }
}
