<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $product = Product::select('updated_at')->orderByDesc('updated_at')->first();
$all = Product::all();
        return inertia('Product/ProductList', [
            'product' => $product,
            'all' => $all,
        ]);
    }

    public function create()
    {
        return inertia('Product/CreateProduct');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sale_price' => 'required|numeric|min:0',
            'status' => 'required',
            'reward_point' => 'nullable|numeric|min:0',
            'set_meal' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $product = Product::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'price' => $request->sale_price,
            'status' => $request->status,
            'reward_point' => $request->reward_point,
            'set_meal' => $request->set_meal,
            'description' => $request->description,
        ]);

        if ($request->hasFile('product_photo')) {
            $product->addMedia($request->product_photo)->toMediaCollection('product_photo');
        }

        return redirect()->route('dashboard')->with('toast', [
            'title' => trans('public.category_created'),
            'message' => trans('public.category_created_caption'). $request->name[app()->getLocale()],
            'type' => 'success'
        ]);
    }
}
