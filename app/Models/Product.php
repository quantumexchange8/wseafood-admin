<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function generateProductCode($category_id)
    {
        $count = ($this->where('category_id', $category_id)->count())+1;
        $formattedCount = str_pad($count, 3, '0', STR_PAD_LEFT);

        $category = Category::find($category_id);
        $prefix = $category->prefix;

        $productCode = $prefix.$formattedCount;

        return $productCode;
    }

    public function hasModifierGroupIds(): HasMany
    {
        return $this->hasMany(ProductToModifierGroup::class, 'product_id', 'id');
    }
}
