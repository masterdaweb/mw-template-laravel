<?php

use App\Support\PlatformStatus;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Runs on the scheduler service (`php artisan schedule:run`, every minute):
// replace it with your own tasks.
Schedule::call(function () {
    Cache::forever(PlatformStatus::SCHEDULER_KEY, now()->toIso8601String());
})->everyMinute()->name('starter-heartbeat');
