<?php

namespace App\Jobs;

use App\Models\PushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OneSignal;

class PushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private object $notification;

    public function __construct(private readonly PushNotification $pushNotification)
    {
        $this->notification = $pushNotification;
        $this->queue = 'push_notification';
    }

    public function handle(): void
    {
        OneSignal::sendNotificationToAll(
            json_decode($this->notification->message, true)['en'],
            $url = null,
            $data = $this->notification->content,
            $buttons = null,
            $schedule = null
        );
    }
}
