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
        Schema::create('portal_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_agency_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            $table->string('source')->default('main_portal');
            $table->string('status')->default('new');
            $table->timestamp('forwarded_at')->nullable();
            $table->timestamps();

            $table->index(['portal_agency_id', 'status']);
            $table->index(['source', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_enquiries');
    }
};
