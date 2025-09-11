<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote');

Schedule::command('push:notification')->everyMinute();
Schedule::command('update:voucher-status')->daily();
Schedule::command('distribute:vouchers')->daily();
