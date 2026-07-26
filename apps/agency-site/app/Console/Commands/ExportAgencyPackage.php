<?php

namespace App\Console\Commands;

use App\Models\Agency;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

#[Signature('agency:export {--output= : Optional export zip path}')]
#[Description('Export the agency website source, database, media, files, and handover notes')]
class ExportAgencyPackage extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agency = Agency::query()->first();

        if (! $agency) {
            $this->error('No agency record found.');

            return self::FAILURE;
        }

        $exportDirectory = storage_path('app/exports');
        File::ensureDirectoryExists($exportDirectory);

        $filename = Str::slug($agency->name).'-export-'.now()->format('Ymd-His').'.zip';
        $outputPath = $this->option('output') ?: $exportDirectory.'/'.$filename;
        File::ensureDirectoryExists(dirname($outputPath));

        $zip = new ZipArchive;

        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error("Unable to create export archive at [{$outputPath}].");

            return self::FAILURE;
        }

        $this->addSourceFiles($zip);
        $this->addIfExists($zip, database_path('database.sqlite'), 'database/database.sqlite');
        $this->addDirectoryIfExists($zip, storage_path('app/public'), 'media/public');
        $this->addDirectoryIfExists($zip, storage_path('app/private'), 'files/private');
        $this->addString($zip, 'metadata/agency.json', json_encode($agency->fresh()->toArray(), JSON_PRETTY_PRINT));
        $this->addString($zip, 'DEPLOYMENT.md', $this->deploymentNotes($agency));

        $zip->close();

        $this->info("Agency export created: {$outputPath}");

        return self::SUCCESS;
    }

    private function addSourceFiles(ZipArchive $zip): void
    {
        $excludedTopLevel = [
            '.env',
            '.git',
            '.DS_Store',
            '.phpunit.result.cache',
            'database/database.sqlite',
            'node_modules',
            'storage/app/private',
            'storage/app/public',
            'storage/app/exports',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'storage/logs',
            'vendor',
        ];

        $root = base_path();
        $files = File::allFiles($root, true);

        foreach ($files as $file) {
            $absolutePath = $file->getPathname();
            $relativePath = Str::replaceFirst($root.'/', '', $absolutePath);

            if ($this->shouldExclude($relativePath, $excludedTopLevel)) {
                continue;
            }

            $zip->addFile($absolutePath, 'source/'.$relativePath);
        }
    }

    private function addDirectoryIfExists(ZipArchive $zip, string $directory, string $zipPrefix): void
    {
        if (! File::isDirectory($directory)) {
            return;
        }

        foreach (File::allFiles($directory, true) as $file) {
            $absolutePath = $file->getPathname();
            $relativePath = Str::replaceFirst($directory.'/', '', $absolutePath);
            $zip->addFile($absolutePath, $zipPrefix.'/'.$relativePath);
        }
    }

    private function addIfExists(ZipArchive $zip, string $absolutePath, string $zipPath): void
    {
        if (File::exists($absolutePath)) {
            $zip->addFile($absolutePath, $zipPath);
        }
    }

    private function addString(ZipArchive $zip, string $zipPath, string $contents): void
    {
        $zip->addFromString($zipPath, $contents.PHP_EOL);
    }

    private function shouldExclude(string $relativePath, array $excluded): bool
    {
        foreach ($excluded as $excludedPath) {
            if ($relativePath === $excludedPath || str_starts_with($relativePath, $excludedPath.'/')) {
                return true;
            }
        }

        return false;
    }

    private function deploymentNotes(Agency $agency): string
    {
        return <<<MARKDOWN
# {$agency->name} Export

This archive contains:

- `source/` - Laravel source code without vendor dependencies or production secrets.
- `database/database.sqlite` - current SQLite database dump for local handover.
- `media/public/` - public media files.
- `files/private/` - private uploaded files, including offer proof documents when present.
- `metadata/agency.json` - agency profile and theme metadata.

Suggested restore flow:

1. Copy `source/` to the target server.
2. Run `composer install` and `npm install`.
3. Copy `.env.example` to `.env` and configure production secrets.
4. Restore `database/database.sqlite` or import the data into PostgreSQL.
5. Copy media and private files back into `storage/app/public` and `storage/app/private`.
6. Run `php artisan key:generate`, `php artisan migrate --force`, and `php artisan storage:link`.

Production keys, platform automation scripts, and main portal code are intentionally not included.
MARKDOWN;
    }
}
