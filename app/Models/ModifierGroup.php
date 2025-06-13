<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'group_name',
        'display_name',
        'group_type',
        'min_selection',
        'max_selection',
        'override',
        'status',
    ];
}
