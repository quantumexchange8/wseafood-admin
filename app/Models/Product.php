<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'category_id',
        'price',
        'status',
        'reward_point',
        'set_meal',
    ];

    protected $casts = [
    'name' => 'array',
    ];
}
