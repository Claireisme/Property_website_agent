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
        Schema::table('agencies', function (Blueprint $table) {
            if (! Schema::hasColumn('agencies', 'company_registration_number')) {
                $table->string('company_registration_number')->nullable()->after('trading_name');
            }

            if (! Schema::hasColumn('agencies', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('theme_key');
            }

            if (! Schema::hasColumn('agencies', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('facebook_url');
            }

            if (! Schema::hasColumn('agencies', 'youtube_url')) {
                $table->string('youtube_url')->nullable()->after('instagram_url');
            }

            if (! Schema::hasColumn('agencies', 'tiktok_url')) {
                $table->string('tiktok_url')->nullable()->after('youtube_url');
            }

            if (! Schema::hasColumn('agencies', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('tiktok_url');
            }

            if (! Schema::hasColumn('agencies', 'x_url')) {
                $table->string('x_url')->nullable()->after('linkedin_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agencies', function (Blueprint $table) {
            $columns = collect([
                'company_registration_number',
                'facebook_url',
                'instagram_url',
                'youtube_url',
                'tiktok_url',
                'linkedin_url',
                'x_url',
            ])->filter(fn (string $column): bool => Schema::hasColumn('agencies', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
