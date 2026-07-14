<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Requires the server's actual cron to run `php artisan schedule:run` every
// minute (standard Laravel deployment step) — see the Site Health widget's
// "Recalculate now" action for an on-demand alternative meanwhile.
Schedule::command('app:check-site-health')->dailyAt('01:00');
