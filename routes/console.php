<?php

use App\Console\Commands\CheckExpiredPendingPaymentReservations;
use App\Console\Commands\DeleteExpiredOffers;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// فحص الحجوزات المنتهية كل دقيقة
// Schedule::command(CheckExpiredPendingPaymentReservations::class)->everyMinute();

// حذف العروض المنتهية يومياً في منتصف الليل
// Schedule::command(DeleteExpiredOffers::class)->daily();
