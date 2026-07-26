<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_settings')) {
            return;
        }

        DB::table('email_settings')
            ->whereIn('provider', ['system', 'smtp', 'brevo'])
            ->update([
                'provider' => 'ses_smtp',
                'smtp_host' => null,
            ]);

        DB::table('email_settings')
            ->whereNull('ses_region')
            ->update(['ses_region' => 'eu-west-1']);

        DB::table('email_settings')
            ->whereNull('smtp_port')
            ->update(['smtp_port' => 587]);

        DB::table('email_settings')
            ->whereNull('smtp_encryption')
            ->update(['smtp_encryption' => 'tls']);

        if (Schema::hasColumn('email_settings', 'brevo_api_key')) {
            Schema::table('email_settings', function (Blueprint $table): void {
                $table->dropColumn('brevo_api_key');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('email_settings') || Schema::hasColumn('email_settings', 'brevo_api_key')) {
            return;
        }

        Schema::table('email_settings', function (Blueprint $table): void {
            $table->text('brevo_api_key')->nullable()->after('smtp_encryption');
        });
    }
};
