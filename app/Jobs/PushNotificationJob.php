<?php

namespace App\Jobs;

use App\Models\PushNotification;
use Auth;
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
        $message = json_decode($this->notification->message, true);
        $title   = json_decode($this->notification->title, true);
        $content = $this->notification->content;

        if (isset($message['cn'])) {
            $message['zh-Hans'] = $message['cn'];
            unset($message['cn']);
        }

        if (isset($title['cn'])) {
            $title['zh-Hans'] = $title['cn'];
            unset($title['cn']);
        }

        OneSignal::sendNotificationCustom([
            'contents' => $message,
            'headings' => $title,
            'included_segments' => ['All'],
            'url' => null,
            'data' => ['contents' => $content],
        ]);

        activity('push_notification')
            ->performedOn($this->notification)
            ->withProperties([
                'title'   => $title,
                'message' => $message,
            ])
            ->event('fired_job')
            ->log('Sent OneSignal notification to all users.');
    }
}
