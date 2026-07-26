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
            if (! Schema::hasColumn('buyer_access_requests', 'initial_offer_amount')) {
                $table->unsignedBigInteger('initial_offer_amount')
                    ->nullable()
                    ->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buyer_access_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('buyer_access_requests', 'initial_offer_amount')) {
                $table->dropColumn('initial_offer_amount');
            }
        });
    }
};
