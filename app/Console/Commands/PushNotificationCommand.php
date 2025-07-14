<?php

namespace App\Console\Commands;

use App\Jobs\PushNotificationJob;
use App\Models\PushNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PushNotificationCommand extends Command
{
    protected $signature = 'push:notification';

    protected $description = 'Push scheduled notification to all users';

    public function handle(): void
    {
        $now = Carbon::now()->format('Y-m-d H:i');

        $notifications = PushNotification::whereRaw("DATE_FORMAT(schedule_datetime, '%Y-%m-%d %H:%i') = ?", [$now])
            ->get();

        foreach ($notifications as $notification) {
            dispatch(new PushNotificationJob($notification));
        }
    }
}
