<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ModifierGroupController extends Controller
{
    public function index()
    {
        return Inertia::render('ModifierGroup/ModifierGroupList');
    }

    public function create()
    {
        return Inertia::render('ModifierGroup/CreateModifierGroup');
    }
}
