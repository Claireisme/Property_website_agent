<?php

namespace App\Console\Commands;

use App\Models\FeedToken;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('feed:issue-token {name=Main Portal} {--expires= : Optional expiry date or relative time, e.g. "2026-12-31" or "+6 months"}')]
#[Description('Issue a one-time visible feed token for the main portal sync worker')]
class IssueFeedToken extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $plainTextToken = FeedToken::generatePlainTextToken();
        $expiresAt = $this->option('expires')
            ? Carbon::parse($this->option('expires'))
            : null;

        FeedToken::query()->create([
            'name' => $this->argument('name'),
            'token_hash' => FeedToken::hashToken($plainTextToken),
            'is_active' => true,
            'expires_at' => $expiresAt,
        ]);

        $this->info('Feed token issued. Store it now; it will not be shown again.');
        $this->line($plainTextToken);

        return self::SUCCESS;
    }
}
