<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Category;

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
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required',
            'description' => 'nullable|string',
        ]);

        $category = Category::create([
            'name' => $request->name,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        if($request->hasFile('category_thumbnail')) {
            $category->addMedia($request->category_thumbnail)->toMediaCollection('category_thumbnail');

        }

        return redirect()->route('category.index')->with('toast', [
            'title' => trans('public.category_created'),
            'message' => trans('public.category_created_caption'). $category->name,
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
                $query->where('status', $data['filters']['status']['value']['value']);
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
                $category->status = $category->status == 1 ? true : false;
                return $category;
            });

            return response()->json([
                'success' => true,
                'data' => $fetchedCategory,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
