<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

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
        $rules = [
            'name' => ['required', 'array'],
            'category_id' => ['required', 'string', 'max:255'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'reward_point' => ['nullable'],
            'set_meal' => ['nullable'],
            'description' => ['nullable'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["name.$locale"] = ['required'];
        }

        $attributeNames = [
            'name.*' => trans('public.item_name'),
            'category_id' => trans('public.category'),
            'sale_price' => trans('public.sale_price'),
            'status' => trans('public.visibility'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

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
