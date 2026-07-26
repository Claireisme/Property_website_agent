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
        Schema::create('agencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trading_name')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->string('psra_licence_number')->nullable()->unique();
            $table->string('website_domain')->nullable()->unique();
            $table->string('logo_path')->nullable();
            $table->string('primary_colour', 20)->default('#0f766e');
            $table->string('secondary_colour', 20)->default('#111827');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('county')->nullable();
            $table->string('eircode')->nullable();
            $table->text('description')->nullable();
            $table->string('theme_key')->default('classic');
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('x_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agencies');
    }
};
