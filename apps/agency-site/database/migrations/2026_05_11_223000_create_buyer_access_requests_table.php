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
        Schema::create('buyer_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_name');
            $table->string('buyer_email');
            $table->string('buyer_phone')->nullable();
            $table->string('status')->default('submitted');
            $table->string('buyer_position')->nullable();
            $table->string('financing_type')->nullable();
            $table->string('mortgage_approval_status')->nullable();
            $table->string('current_property_status')->nullable();
            $table->string('proof_of_funds_path')->nullable();
            $table->string('identity_document_path')->nullable();
            $table->text('message')->nullable();
            $table->boolean('consent_to_terms')->default(false);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('documents_uploaded_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->index(['property_id', 'status']);
            $table->index(['buyer_email', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyer_access_requests');
    }
};
