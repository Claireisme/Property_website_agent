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
        Schema::create('portal_property_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('portal_property_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 8);
            $table->string('status')->default('machine_translated');
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->string('source_hash', 64);
            $table->text('error_message')->nullable();
            $table->timestamp('translated_at')->nullable();
            $table->timestamps();

            $table->unique(['portal_property_id', 'locale']);
            $table->index(['locale', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_property_translations');
    }
};
