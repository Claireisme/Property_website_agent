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
        Schema::table('portal_properties', function (Blueprint $table) {
            $table->boolean('online_offers_enabled')->default(false)->after('features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portal_properties', function (Blueprint $table) {
            $table->dropColumn('online_offers_enabled');
        });
    }
};
