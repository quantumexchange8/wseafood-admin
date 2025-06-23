<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PushNotification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'message',
        'content',
        'schedule_type',
        'schedule_datetime',
        'status',
    ];
}
