<?php

namespace App\Console\Commands;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurgeStaleUnverifiedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:purge-unverified {--dry-run : Output counts without deleting records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unverified accounts and stale OTPs that are older than the configured retention window.';

    public function handle(): int
    {
        $retentionDays = (int) config('cleanup.unverified_user_retention_days', 14);
        $chunkSize = (int) config('cleanup.unverified_user_chunk_size', 100);

        if ($retentionDays <= 0) {
            $this->info('Retention is disabled (<= 0 days); skipping purge.');
            return self::SUCCESS;
        }

        $cutoff = Carbon::now()->subDays($retentionDays);

        $query = User::query()
            ->whereNull('email_verified_at')
            ->whereNull('phone_verified_at')
            ->where('created_at', '<=', $cutoff)
            ->where('role', '!=', 'admin');

        $totalCandidates = (clone $query)->count();

        if ($totalCandidates === 0) {
            $this->info('No stale unverified users found.');
            return self::SUCCESS;
        }

        $this->info(sprintf('Found %d unverified user(s) older than %d day(s).', $totalCandidates, $retentionDays));

        if ($this->option('dry-run')) {
            $this->line('Dry-run mode enabled; no records were deleted.');
            return self::SUCCESS;
        }

        $deletedUsers = 0;
        $deletedOtps = 0;

        $query->chunkById($chunkSize, function ($users) use (&$deletedUsers, &$deletedOtps) {
            DB::transaction(function () use ($users, &$deletedUsers, &$deletedOtps) {
                $userIds = $users->pluck('id');

                $deletedOtps += Otp::whereIn('user_id', $userIds)->delete();
                $deletedUsers += User::whereIn('id', $userIds)->delete();
            });
        });

        $this->info(sprintf('Deleted %d user(s) and %d OTP record(s).', $deletedUsers, $deletedOtps));
        Log::info('accounts:purge-unverified completed', [
            'deleted_users' => $deletedUsers,
            'deleted_otps' => $deletedOtps,
            'retention_days' => $retentionDays,
        ]);

        return self::SUCCESS;
    }
}
