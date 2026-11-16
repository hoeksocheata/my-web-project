<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ជូនដំណឹងស្តុកជិតអស់ រាល់ព្រឹក ម៉ោង 8 (ត្រូវដំណើរការ `php artisan schedule:work` ឬ cron)
Illuminate\Support\Facades\Schedule::command('books:low-stock')->dailyAt('08:00');
