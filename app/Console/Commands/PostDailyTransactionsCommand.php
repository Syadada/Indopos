<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Services\DailyPostingService;
use Illuminate\Console\Command;

class PostDailyTransactionsCommand extends Command
{
    protected $signature = 'pos:post-daily {--date= : The date to post (YYYY-MM-DD), defaults to today}';

    protected $description = 'Post completed transactions for a specific date into Data Master Posting Harian';

    public function handle()
    {
        $date = $this->option('date') ?: date('Y-m-d');
        $branches = Branch::where('is_active', true)->get();

        $this->info("Starting daily posting for date: {$date} across " . $branches->count() . " branches...");

        foreach ($branches as $branch) {
            $posting = DailyPostingService::postForDate($branch->id, $date);
            $this->line(" - Branch: {$branch->name} -> Kode: {$posting->posting_code} | Money In: Rp " . number_format($posting->total_money_in, 0, ',', '.') . " | Money Out: Rp " . number_format($posting->total_money_out, 0, ',', '.'));
        }

        $this->info('Daily posting process completed successfully.');
        return 0;
    }
}
