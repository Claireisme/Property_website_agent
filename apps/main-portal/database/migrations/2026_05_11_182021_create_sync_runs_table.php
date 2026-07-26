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
        Schema::create('sync_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_agency_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('success');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('listings_seen')->default(0);
            $table->unsignedInteger('listings_created')->default(0);
            $table->unsignedInteger('listings_updated')->default(0);
            $table->unsignedInteger('listings_removed')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['portal_agency_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_runs');
    }
};
