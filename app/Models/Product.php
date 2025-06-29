<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'product_code',
        'name',
        'category_id',
        'price',
        'status',
        'reward_point',
        'set_meal',
    ];

    public function generateProductCode($category_id): string
    {
        $count = ($this->where('category_id', $category_id)->count())+1;
        $formattedCount = str_pad($count, 3, '0', STR_PAD_LEFT);

        $category = Category::find($category_id);
        $prefix = $category->prefix;

        return $prefix.$formattedCount;
    }

    public function hasModifierGroupIds(): HasMany
    {
        return $this->hasMany(ProductToModifierGroup::class, 'product_id', 'id');
    }

    public function viewAnalytics(): MorphMany
    {
        return $this->morphMany(ViewAnalytic::class, 'subject');
    }
}
