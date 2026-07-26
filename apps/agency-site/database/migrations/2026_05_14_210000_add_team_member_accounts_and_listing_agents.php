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
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('admin')->after('password')->index();
            }
        });

        Schema::table('team_members', function (Blueprint $table): void {
            if (! Schema::hasColumn('team_members', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('agency_id')
                    ->unique()
                    ->constrained()
                    ->nullOnDelete();
            }
        });

        Schema::table('properties', function (Blueprint $table): void {
            if (! Schema::hasColumn('properties', 'team_member_id')) {
                $table->foreignId('team_member_id')
                    ->nullable()
                    ->after('agency_id')
                    ->constrained('team_members')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table): void {
            if (Schema::hasColumn('properties', 'team_member_id')) {
                $table->dropConstrainedForeignId('team_member_id');
            }
        });

        Schema::table('team_members', function (Blueprint $table): void {
            if (Schema::hasColumn('team_members', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
