<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ModifierGroup;
use App\Models\ModifierItem;
use App\Models\ModifierItemToGroup;
use App\Models\ProductToModifierGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ModifierController extends Controller
{
    public function index()
    {
        return Inertia::render('ModifierGroup/ModifierGroupList');
    }

    public function create()
    {
        $item_count = ModifierItem::count();
        $category_count = Category::count();

        return Inertia::render('ModifierGroup/CreateModifierGroup', [
            'itemCount' => $item_count,
            'categoryCount' => $category_count,
        ]);
    }

    public function storeGroup(Request $request)
    {
        $rules = [
            'group_name' => ['required', 'string'],
            'display_name' => ['required', 'array'],
            'group_type' => ['required', 'string'],
            'min' => ['required', 'numeric', 'min:0'],
            'max' => ['sometimes', 'numeric', 'min:0'],
            'modifier_items' => ['required', 'array'],
            'meals' => ['sometimes', 'required', 'array'],
            'default_item' => ['nullable'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["display_name.$locale"] = ['required'];
        }

        $attributeNames = [
            'group_name' => trans('public.group_name'),
            'display_name.*' => trans('public.display_name'),
            'group_type' => trans('public.group_type'),
            'min' => trans('public.min'),
            'max' => trans('public.max'),
            'modifier_items' => trans('public.modifier_item'),
            'meals' => trans('public.meal_item'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $modifierGroup = ModifierGroup::create([
            'group_name' => $request->group_name,
            'display_name' => json_encode($request->display_name),
            'group_type' => $request->group_type,
            'min_selection' => $request->min,
            'max_selection' => $request->max,
            'status' => 'active',
        ]);

        foreach($request->modifier_items as $key => $item) {
            ModifierItemToGroup::create([
                'modifier_group_id' => $modifierGroup->id,
                'modifier_item_id' => $item['id'], 
                'position' => $key+1,
                'status' => $item['status'],
                'price' => $item['price'],
                'default' => $request->default_item === $item['id'] ? true : false,
            ]);
        };

        if($request->meals) {
            foreach($request->meals as $product) {
                ProductToModifierGroup::create([
                    'product_id' => $product['id'],
                    'modifier_group_id' => $modifierGroup->id,
                ]);
            }
        }

        return redirect()->route('modifier.group.index')->with('toast',[
            'title' => trans('public.modifier_group_created'),
            'message' => trans('public.modifier_group_created_caption'). $request->group_name,
            'type' => 'success'
        ]);
    }

    public function storeItem(Request $request)
    {
        $rules = [
            'modifier_name' => ['required', 'array'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["modifier_name.$locale"] = ['required'];
        }

        $attributeNames = [
            'modifier_name.*' => trans('public.modifier_name'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $item = ModifierItem::create([
            'modifier_name' => json_encode($request->modifier_name),
            'slug' => Str::slug($request->modifier_name['en']),
        ]);

        return redirect()->back()->with('toast', [
            'title' => trans('public.modifier_item_created'),
            'message' => trans('public.modifier_item_created_caption'). $request->modifier_name[app()->getLocale()],
            'type' => 'success'
        ]);
    }

    public function fetchModifierItem(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = ModifierItem::query();

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('modifier_name', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $fetchedItem = $query->get();

            return response()->json([
                'success' => true,
                'data' => $fetchedItem,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function fetchCategoryProduct()
    {
        $data = Category::all()->map(function($category) {
            $products = $category->products()->get()->map(function($product) {
                return [
                    'key' => $product->product_code,
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'product_photo' => $product->getFirstMediaUrl('product_photo'),
                    'type' => 'meal',
                ];
            });
            
            return [
                'key' => $category->id,
                'name' => $category->name,
                'children' => $products,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }
}
