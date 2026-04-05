<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendWhatsAppRemindersJob;
use App\Jobs\DispatchScheduledCampaignsJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| WhatsApp Scheduler
|--------------------------------------------------------------------------
*/
Schedule::job(new SendWhatsAppRemindersJob, 'whatsapp')->hourly();
Schedule::job(new DispatchScheduledCampaignsJob, 'whatsapp')->everyFiveMinutes();
