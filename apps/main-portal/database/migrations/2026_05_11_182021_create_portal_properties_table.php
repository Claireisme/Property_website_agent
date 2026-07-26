<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portal_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_agency_id')->constrained()->cascadeOnDelete();
            $table->string('external_listing_id');
            $table->string('source_url')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status');
            $table->string('transaction_type')->nullable();
            $table->string('property_type')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->decimal('floor_area_m2', 8, 2)->nullable();
            $table->string('ber_rating')->nullable();
            $table->string('address_summary')->nullable();
            $table->string('town')->nullable();
            $table->string('county')->nullable();
            $table->string('eircode_hash')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->longText('description')->nullable();
            $table->json('images')->nullable();
            $table->json('features')->nullable();
            $table->timestamp('source_updated_at')->nullable();
            $table->timestamp('first_synced_at')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['portal_agency_id', 'external_listing_id']);
            $table->index(['status', 'transaction_type']);
            $table->index(['town', 'county']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_properties');
    }
};
