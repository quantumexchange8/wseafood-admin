<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Category/CategoryList');
    }

    public function create()
    {
        return Inertia::render('Category/CreateCategory');
    }
}
