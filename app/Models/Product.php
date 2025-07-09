<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes, LogsActivity;

    protected $fillable = [
        'product_code',
        'name',
        'category_id',
        'price',
        'status',
        'reward_point',
        'set_meal',
        'description',
    ];

    public function generateProductCode($category_id): string
    {
        $count = ($this->where('category_id', $category_id)->count())+1;
        $formattedCount = str_pad($count, 3, '0', STR_PAD_LEFT);

        $category = Category::find($category_id);
        $prefix = $category->prefix;

        return $prefix.$formattedCount;
    }

    public function category(): belongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function hasModifierGroupIds(): HasMany
    {
        return $this->hasMany(ProductToModifierGroup::class, 'product_id', 'id');
    }

    public function viewAnalytics(): MorphMany
    {
        return $this->morphMany(ViewAnalytic::class, 'subject');
    }

    public function modifier_groups(): BelongsToMany
    {
        return $this->belongsToMany(
            ModifierGroup::class,
            'product_to_modifier_groups',
            'product_id',
            'modifier_group_id'
        );
    }

    //Log
    public function getActivitylogOptions(): LogOptions
    {
        $user = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('product')
            ->logOnly([
                'product_code',
                'name',
                'category_id',
                'price',
                'status',
                'reward_point',
                'set_meal',
                'description',
            ])
            ->setDescriptionForEvent(function (string $eventName) use ($user) {
                $actorName = Auth::user() ? Auth::user()->full_name : 'System';
                return "{$actorName} has {$eventName} product with ID: {$user->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
