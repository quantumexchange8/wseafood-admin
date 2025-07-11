<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $category = Category::select('updated_at')->orderByDesc('updated_at')->first();
        $categoryCount = Category::all()->count();

        return Inertia::render('Category/CategoryList', [
            'category' => $category,
            'categoryCount' => $categoryCount,
        ]);
    }

    public function create()
    {
        return Inertia::render('Category/CreateCategory');
    }

    public function store(Request $request)
    {
        $rules = [
            'prefix' => ['required', 'string', 'max:50'],
            'name' => ['required', 'array'],
            'status' => ['required', 'string'],
            'description' => ['nullable'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["name.$locale"] = ['required'];
        }

        $attributeNames = [
            'prefix' => trans('public.category_prefix'),
            'name.*' => trans('public.category_name'),
            'status' => trans('public.visibility'),
            'description' => trans('public.description'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $category = Category::create([
            'name' => json_encode($request->name),
            'prefix' => $request->prefix,
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
        } else {
            $categories = Category::where('status', 'active')->get()->map(function ($category) {
                $category->category_thumbnail = $category->getFirstMediaUrl('category_thumbnail');
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'category_thumbnail' => $category->category_thumbnail,
                ];
            });

            return response()->json(['success' => true, 'data' => $categories]);
        }
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
        $name = json_decode($category->name)->$locale;

        $category->products()->update([
            'status' => $category->status,
        ]);

        return redirect()->back()->with('toast', [
            'title' => trans('public.status_updated'),
            'message' => trans('public.status_updated_caption'). $name,
            'type' => 'success'
        ]);
    }

    public function edit(Request $request)
    {
        $category = Category::find($request->id);
        $category->name = json_decode($category->name);
        $category->category_thumbnail = $category->getFirstMediaUrl('category_thumbnail');

        return Inertia::render('Category/EditCategory', [
            'category' => $category,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => ['required'],
            'prefix' => ['required', 'string', 'max:50'],
            'name' => ['required', 'array'],
            'status' => ['required', 'string'],
            'description' => ['nullable'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["name.$locale"] = ['required'];
        }

        $attributeNames = [
            'id' => trans('public.id'),
            'prefix' => trans('public.category_prefix'),
            'name.*' => trans('public.category_name'),
            'status' => trans('public.visibility'),
            'description' => trans('public.description'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $category = Category::find($request->id);
        if ($request->hasFile('category_thumbnail')) {
            $category->clearMediaCollection('category_thumbnail');
            $category->addMedia($request->category_thumbnail)->toMediaCollection('category_thumbnail');

            activity('category')
                ->causedBy(Auth::user())
                ->performedOn($category)
                ->withProperties([
                    'action' => 'updated category image',
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                ])
                ->event('updated')
                ->log(Auth::user()->full_name . ' updated category image for category ID: ' . $category->id);
        }

        if ($request->photo_action == 'remove') {
            $category->clearMediaCollection('category_thumbnail');

            activity('category')
                ->causedBy(Auth::user())
                ->performedOn($category)
                ->withProperties([
                    'action' => 'updated category image',
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                ])
                ->event('deleted')
                ->log(Auth::user()->full_name . ' remove category image for category ID: ' . $category->id);
        }

        $category->update([
            'name' => json_encode($request->name),
            'prefix' => $request->prefix,
            'status' => $request->status,
            'description' => $request->description,
        ]);

        return redirect()->route('category.index')->with('toast', [
            'title' => trans('public.category_updated'),
            'message' => trans('public.category_updated_caption'). $request->name[app()->getLocale()],
            'type' => 'success'
        ]);
    }

    public function destroy(Request $request)
    {
        $category = Category::find($request->id);
        $name = json_decode($category->name);
        $locale = app()->getLocale();
        $category->delete();

        return redirect()->back()->with('toast', [
            'title' => trans('public.category_deleted'),
            'message' => trans('public.category_deleted_caption'). $name->$locale,
            'type' => 'success'
        ]);
    }
}
