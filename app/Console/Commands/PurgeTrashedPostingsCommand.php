<?php

namespace App\Console\Commands;

use App\Models\DailyPosting;
use Illuminate\Console\Command;

class PurgeTrashedPostingsCommand extends Command
{
    protected $signature = 'pos:purge-trashed {--days=30 : Number of days soft deleted before permanent purge}';

    protected $description = 'Permanently purge soft deleted daily postings older than specified days (default 30 days / 1 month)';

    public function handle()
    {
        $days = (int) ($this->option('days') ?: 30);
        $thresholdDate = now()->subDays($days);

        $trashed = DailyPosting::onlyTrashed()
            ->where('deleted_at', '<=', $thresholdDate)
            ->with('items')
            ->get();

        $count = $trashed->count();
        $this->info("Found {$count} soft-deleted master posting(s) older than {$days} days (before {$thresholdDate->format('d M Y')}).");

        foreach ($trashed as $posting) {
            $code = $posting->posting_code;
            $posting->items()->forceDelete();
            $posting->forceDelete();
            $this->line(" - Purged permanently: {$code}");
        }

        $this->info("Permanent purge completed successfully.");
        return 0;
    }
}
