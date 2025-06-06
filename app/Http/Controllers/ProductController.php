<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return inertia('Product/ProductList');
    }

    public function create()
    {
        return inertia('Product/CreateProduct');
    }
}
