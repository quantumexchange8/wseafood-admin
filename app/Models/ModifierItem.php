<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug'
    ];

    public function hasModifierGroupIds(): HasMany
    {
        return $this->hasMany(ModifierItemToGroup::class, 'modifier_item_id', 'id');
    }
}
