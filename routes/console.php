<?php

use App\Models\ListeningParty;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// set schedule to run every minute to set completed listening parties to inactive
Schedule::call(function () {
    ListeningParty::where('end_time', '<=', now())->update(['is_active' => false]);
})->everyMinute();
