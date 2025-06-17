<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class CleanExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:clean-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired and unused tokens from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting token cleanup...');

        try {
            // Delete tokens that have expired
            $expiredCount = PersonalAccessToken::where('expires_at', '<', now())
                ->delete();

            $this->info("Deleted {$expiredCount} expired tokens");

            // Delete tokens that haven't been used in 7 days
            $unusedCount = PersonalAccessToken::where('last_used_at', '<', now()->subDays(7))
                ->delete();

            $this->info("Deleted {$unusedCount} unused tokens");

            // Log the cleanup
            Log::info('Token cleanup completed', [
                'expired_tokens_deleted' => $expiredCount,
                'unused_tokens_deleted' => $unusedCount,
                'total_deleted' => $expiredCount + $unusedCount
            ]);

            $this->info('Token cleanup completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            Log::error('Token cleanup failed', [
                'error' => $e->getMessage()
            ]);

            $this->error('Token cleanup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
