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
        Schema::table('buyer_access_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('buyer_access_requests', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('property_id')
                    ->constrained()
                    ->nullOnDelete();

                $table->index(['user_id', 'property_id', 'status']);
            }
        });

        Schema::table('offers', function (Blueprint $table): void {
            if (! Schema::hasColumn('offers', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('property_id')
                    ->constrained()
                    ->nullOnDelete();

                $table->index(['user_id', 'property_id', 'status']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table): void {
            if (Schema::hasColumn('offers', 'user_id')) {
                $table->dropIndex(['user_id', 'property_id', 'status']);
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('buyer_access_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('buyer_access_requests', 'user_id')) {
                $table->dropIndex(['user_id', 'property_id', 'status']);
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
