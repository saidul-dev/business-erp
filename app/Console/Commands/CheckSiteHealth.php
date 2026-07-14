<?php

namespace App\Console\Commands;

use App\Services\SiteHealthChecker;
use Illuminate\Console\Command;

class CheckSiteHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-site-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recompute the Site Health score (data consistency, inventory accuracy, financial integrity, pending backlog) shown in the admin topbar';

    /**
     * Execute the console command.
     */
    public function handle(SiteHealthChecker $checker): int
    {
        $snapshot = $checker->run();

        $this->info("Site health computed: {$snapshot->overall_score}% overall.");

        return self::SUCCESS;
    }
}
