<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Highlight extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'title',
        'content',
        'status',
        'position',
        'can_popup',
        'url',
    ];

    public function getPosition(): int
    {
        return ($this->all()->count())+1;
    }

    public function viewAnalytics(): MorphMany
    {
        return $this->morphMany(ViewAnalytic::class, 'subject');
    }
}
