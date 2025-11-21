<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RefreshToken;
use Carbon\Carbon;

class CleanupRefreshTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-refresh-tokens {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired and revoked refresh tokens';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting refresh tokens cleanup...');

        $dryRun = $this->option('dry-run');

        // Clean up revoked tokens older than 30 days
        $revokedTokens = RefreshToken::where('revoked', true)
            ->where('updated_at', '<', Carbon::now()->subDays(30));

        $revokedCount = $revokedTokens->count();

        if ($dryRun) {
            $this->info("Would delete {$revokedCount} revoked tokens (older than 30 days)");
        } else {
            if ($revokedCount > 0) {
                $revokedTokens->delete();
                $this->info("Deleted {$revokedCount} revoked tokens (older than 30 days)");
            } else {
                $this->info('No old revoked tokens to delete');
            }
        }

        // Clean up expired tokens (including non-revoked ones)
        $expiredTokens = RefreshToken::where('expires_at', '<', Carbon::now());
        $expiredCount = $expiredTokens->count();

        if ($dryRun) {
            $this->info("Would delete {$expiredCount} expired tokens");
        } else {
            if ($expiredCount > 0) {
                $expiredTokens->delete();
                $this->info("Deleted {$expiredCount} expired tokens");
            } else {
                $this->info('No expired tokens to delete');
            }
        }

        $totalCleaned = $revokedCount + $expiredCount;

        if ($dryRun) {
            $this->line("Dry run complete. Would delete total {$totalCleaned} tokens.");
        } else {
            $this->info("Cleanup complete! Deleted total {$totalCleaned} tokens.");
        }

        return 0;
    }
}
