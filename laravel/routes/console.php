<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SendDocumentExpiryNotification;
use App\Jobs\SendAccesssoryExpiryNotification;
use App\Jobs\SendDriverLicenseExpiryNotification;
use App\Jobs\SendOrderRequiresApprovalNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Jobs
Schedule::job(new SendDocumentExpiryNotification)->weekly();

Schedule::job(new SendOrderRequiresApprovalNotification)->weekly();

Schedule::job(new SendAccesssoryExpiryNotification)->weekly();

Schedule::job(new SendDriverLicenseExpiryNotification)->weekly();