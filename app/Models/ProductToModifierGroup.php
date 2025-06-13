<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductToModifierGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'modifier_group_id',
    ];

}
