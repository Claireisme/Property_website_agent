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
        Schema::create('translation_provider_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->unique();
            $table->boolean('is_enabled')->default(false);
            $table->text('api_key')->nullable();
            $table->string('base_url')->default('https://api.deepseek.com');
            $table->string('model')->default('deepseek-chat');
            $table->unsignedInteger('timeout_seconds')->default(90);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_provider_settings');
    }
};
