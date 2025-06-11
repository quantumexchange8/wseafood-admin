<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;

class ProductController extends Controller
{
    public function index()
    {
        $product_last_update = Product::select('updated_at')->orderByDesc('updated_at')->first();
        $product_count = Product::count();
        $categories = Category::select('id', 'name')->get();

        return inertia('Product/ProductList', [
            'product' => $product_last_update,
            'categories' => $categories,
            'productCount' => $product_count,
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
            'status' => 'required|string',
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

        return redirect()->back()->with('toast', [
            'title' => trans('public.category_created'),
            'message' => trans('public.category_created_caption'). $request->name[app()->getLocale()],
            'type' => 'success'
        ]);
    }

    public function fetchProduct(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = Product::query();

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            }

            // status
            if ($data['filters']['status']['value']) {
                $query->where('status', $data['filters']['status']['value']);
            }

            //Category
            if ($data['filters']['category']['value']) {
                $query->where('category_id', $data['filters']['category']['value']);
            }

            // if ($data['sortField'] && $data['sortOrder']) {
            //     $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
            //     $query->orderBy($data['sortField'], $order);
            // } else {
            //     $query->orderByDesc('created_at');
            // }

            $fetchedProduct = $query->paginate($data['rows']);

            $fetchedProduct->getCollection()->transform(function($product) {
                $product->product_photo = $product->getFirstMediaUrl('product_photo');
                return $product;
            });

            return response()->json([
                'success' => true,
                'data' => $fetchedProduct,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
