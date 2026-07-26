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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained()->cascadeOnDelete();
            $table->string('public_id')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->string('transaction_type')->default('sale');
            $table->string('property_type')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->string('price_qualifier')->default('asking_price');
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->decimal('floor_area_m2', 8, 2)->nullable();
            $table->string('ber_rating')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('town')->nullable();
            $table->string('county')->nullable();
            $table->string('eircode')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->longText('description')->nullable();
            $table->json('features')->nullable();
            $table->text('viewing_notes')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('sale_agreed_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->index(['agency_id', 'status']);
            $table->index(['transaction_type', 'property_type']);
            $table->index(['town', 'county']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
