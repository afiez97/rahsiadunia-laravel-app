<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hantar peringatan hutang setiap hari pukul 8 pagi
Schedule::command('telegram:send-due-warnings')->dailyAt('08:00');
