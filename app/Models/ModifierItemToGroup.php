<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierItemToGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'modifier_group_id',
        'modifier_item_id',
        'position',
        'price',
        'default',
    ];

}
