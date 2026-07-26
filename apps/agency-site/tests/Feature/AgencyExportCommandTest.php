<?php

namespace Tests\Feature;

use App\Models\Agency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ZipArchive;

class AgencyExportCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_agency_export_command_creates_handover_archive(): void
    {
        Agency::query()->create([
            'name' => 'Export Test Agency',
        ]);

        $outputPath = storage_path('app/testing/export-test-agency.zip');
        File::delete($outputPath);

        $this->artisan('agency:export', [
            '--output' => $outputPath,
        ])->assertExitCode(0);

        $this->assertFileExists($outputPath);

        $zip = new ZipArchive;
        $zip->open($outputPath);

        $this->assertNotFalse($zip->locateName('DEPLOYMENT.md'));
        $this->assertNotFalse($zip->locateName('metadata/agency.json'));
        $this->assertNotFalse($zip->locateName('source/composer.json'));

        $zip->close();
    }
}
