<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-mark overdue rentals daily
Schedule::call(function () {
    \App\Models\Rental::where('status', 'active')
        ->where('due_date', '<', now()->toDateString())
        ->update(['status' => 'overdue']);
})->daily();
