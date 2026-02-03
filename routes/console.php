<?php

use App\Jobs\RunLeadSourceDiscovery;
use App\Models\LeadSource;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Phase 2a: Discovery scheduler (daily 07:00, weekly Monday 07:30 Europe/London)
Schedule::call(function () {
    $daily = LeadSource::where('cadence', 'daily')->where('status', 'enabled')->get();
    foreach ($daily as $source) {
        RunLeadSourceDiscovery::dispatch($source, 'scheduled');
    }
})->dailyAt('07:00')->timezone('Europe/London');

Schedule::call(function () {
    $weekly = LeadSource::where('cadence', 'weekly')->where('status', 'enabled')->get();
    foreach ($weekly as $source) {
        RunLeadSourceDiscovery::dispatch($source, 'scheduled');
    }
})->weeklyOn(1, '07:30')->timezone('Europe/London');
