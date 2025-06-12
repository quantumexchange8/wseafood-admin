<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::select('updated_at')->orderByDesc('updated_at')->first();

        return Inertia::render('Category/CategoryList', [
            'category' => $category
        ]);
    }

    public function create()
    {
        return Inertia::render('Category/CreateCategory');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required', 'array'],
            'status' => ['required', 'string'],
            'description' => ['nullable'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["name.$locale"] = ['required'];
        }

        $attributeNames = [
            'name.*' => trans('public.category_name'),
            'status' => trans('public.visibility'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $category = Category::create([
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        if($request->hasFile('category_thumbnail')) {
            $category->addMedia($request->category_thumbnail)->toMediaCollection('category_thumbnail');
        }

        return redirect()->back()->with('toast', [
            'title' => trans('public.category_created'),
            'message' => trans('public.category_created_caption'). $request->name[app()->getLocale()],
            'type' => 'success'
        ]);
    }

    public function fetchCategory(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = Category::query();

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

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $fetchedCategory = $query->paginate($data['rows']);

            $fetchedCategory->getCollection()->transform(function ($category) {
                $category->category_thumbnail = $category->getFirstMediaUrl('category_thumbnail');
                $category->product_count = $category->products()->count();
                return $category;
            });

            return response()->json([
                'success' => true,
                'data' => $fetchedCategory,
            ]);
        }
        else {
            $categories = Category::all()->map(function ($category) {
                $category->category_thumbnail = $category->getFirstMediaUrl('category_thumbnail');
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'category_thumbnail' => $category->category_thumbnail,
                ];
            });

            return response()->json(['success' => true, 'data' => $categories]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function updateStatus(Request $request)
    {
        $category = Category::find($request->id);

        if($request->status === 'active') {
            $category->status = 'inactive';
        } else {
            $category->status = 'active';
        }
        $category->save();

        $locale = app()->getLocale();

        return redirect()->back()->with('toast', [
            'title' => trans('public.status_updated'),
            'message' => trans('public.status_updated_caption'). json_decode($request->name)->$locale,
            'type' => 'success'
        ]);
    }
}
