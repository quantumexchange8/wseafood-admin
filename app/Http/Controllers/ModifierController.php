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
        $modifierGroup = ModifierGroup::select('updated_at')->orderByDesc('updated_at')->first();
        $groupCount = ModifierGroup::all()->count();

        return Inertia::render('ModifierGroup/ModifierGroupList', [
            'modifierGroup' => $modifierGroup,
            'groupCount' => $groupCount,
        ]);
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
            'max' => ['nullable', 'numeric', 'min:0'],
            'modifier_items' => ['required', 'array'],
            'meals' => ['nullable', 'array'],
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

    public function fetchModifierGroup(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = ModifierGroup::query();

            if ($data['filters']['itemRange']['value']) {
                $range = $data['filters']['itemRange']['value'];
                if (count($range) === 2) {
                    $query->withCount(['hasModifierItemIds as item_count']);
                    $query->havingBetween('item_count', [$range[0], $range[1]]);
                }
            }

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('group_name', 'like', '%' . $keyword . '%')->orWhere('display_name', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['filters']['status']['value']) {
                $query->where('status', $data['filters']['status']['value']);
            }

            if ($data['filters']['groupType']['value']) {
                $query->where('group_type', $data['filters']['groupType']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $fetchedGroup = null;
            if($data['rows']) {
                $fetchedGroup = $query->paginate($data['rows']);
                $fetchedGroup->getCollection()->map(function ($group) {
                    $group->product_count = $group->hasProductIds()->count();
                    $group->item_count = $group->hasModifierItemIds()->count();
                    $group->items = $group->hasModifierItemIds()->orderBy('position')->get()->map(function ($item) {
                        return [
                            'modifier_item_id' => $item->modifier_item_id,
                            'modifier_item_name' => $item->modifierItem()->first()->name,
                        ];
                    });
                    return $group;
                });
            } else {
                $fetchedGroup = $query->get()->map(function ($group) {
                    $group->product_count = $group->hasProductIds()->count();
                    $group->item_count = $group->hasModifierItemIds()->count();
                    $group->items = $group->hasModifierItemIds()->orderBy('position')->get()->map(function ($item) {
                        return [
                            'modifier_item_id' => $item->modifier_item_id,
                            'modifier_item_name' => $item->modifierItem()->first()->name,
                        ];
                    });
                    return $group;
                });
            }

            return response()->json([
                'success' => true,
                'data' => $fetchedGroup,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function updateGroupStatus(Request $request)
    {
        $group = ModifierGroup::find($request->id);

        if($request->status === 'active') {
            $group->status = 'inactive';
        } else {
            $group->status = 'active';
        }
        $group->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.status_updated'),
            'message' => trans('public.status_updated_caption'). $request->name,
            'type' => 'success'
        ]);
    }

    public function editGroup(Request $request)
    {
        $item_count = ModifierItem::count();
        $category_count = Category::count();

        $group = ModifierGroup::find($request->id);
        $group->display_name = json_decode($group->display_name);

        $item = $group->hasModifierItemIds()->orderBy('position')->get()->transform(function ($id) {
            $data = $id->modifierItem()->first();
            $data->original_item = $data->getOriginal();
            $data->status = $id->status;
            $data->price = $id->price;
            $data->default = $id->default;
            return $data;
        });

        $productIds = $group->hasProductIds()->pluck('product_id')->toArray();

        $categories = Category::all();
        $selectedCategoryTree = $categories->map(function($category) use ($productIds) {
            $products = $category->products()->whereIn('id', $productIds)->get()->map(function($product) {
                return [
                    'key' => $product->product_code,
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'product_photo' => $product->getFirstMediaUrl('product_photo'),
                    'type' => 'meal',
                ];
            });

            if ($products->count() > 0) {
                return [
                    'key' => $category->id,
                    'name' => $category->name,
                    'children' => $products,
                ];
            }
            return null;
        })->filter()->values();

        $selectedProductKeys = [];
        foreach ($selectedCategoryTree as $category) {
            foreach ($category['children'] as $product) {
                $selectedProductKeys[$product['key']] = ['checked' => true, 'partialChecked' => false];
            }

            $product_count = Category::find($category['key'])->products()->count();
            if(count($category['children']) === $product_count) {
                $selectedProductKeys[$category['key']] = ['checked' => true, 'partialChecked' => false];
            } else {
                $selectedProductKeys[$category['key']] = ['checked' => false, 'partialChecked' => true];
            }
        }

        $selectedProducts = collect($selectedCategoryTree)->flatMap(function($category) {
            return $category['children'];
        })->values();

        return Inertia::render('ModifierGroup/EditModifierGroup', [
            'itemCount' => $item_count,
            'categoryCount' => $category_count,
            'modifierGroup' => $group,
            'selectedItem' => $item,
            'selectedProduct' => $selectedProducts,
            'selectedProductKeys' => $selectedProductKeys,
        ]);
    }

    public function updateGroup(Request $request)
    {
        $rules = [
            'id' => ['required'],
            'group_name' => ['required', 'string'],
            'display_name' => ['required', 'array'],
            'group_type' => ['required', 'string'],
            'min' => ['required', 'numeric', 'min:0'],
            'max' => ['nullable', 'numeric', 'min:0'],
            'modifier_items' => ['required', 'array'],
            'meals' => ['nullable', 'array'],
            'default_item' => ['nullable'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["display_name.$locale"] = ['required'];
        }

        $attributeNames = [
            'id' => trans('public.id'),
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

        $modifierGroup = ModifierGroup::find($request->id);
        $modifierGroup->update([
            'group_name' => $request->group_name,
            'display_name' => json_encode($request->display_name),
            'group_type' => $request->group_type,
            'min_selection' => $request->min,
            'max_selection' => $request->max,
        ]);

        foreach($request->modifier_items as $key => $item) {
            ModifierItemToGroup::updateOrCreate([
                'modifier_group_id' => $request->id,
                'modifier_item_id' => $item['id'],
                ], [ 
                'position' => $key+1,
                'status' => $item['status'],
                'price' => $item['price'],
                'default' => $request->default_item === $item['id'] ? true : false,
            ]);
        };

        $currentProductIds = ProductToModifierGroup::where('modifier_group_id', $request->id)->pluck('product_id')->toArray();

        $newProductIds = collect($request->meals)->pluck('id')->toArray();

        $toAdd = array_diff($newProductIds, $currentProductIds);
        $toRemove = array_diff($currentProductIds, $newProductIds);

        if(!empty($toAdd)) {
            foreach ($toAdd as $productId) {
                ProductToModifierGroup::create([
                    'product_id' => $productId,
                    'modifier_group_id' => $modifierGroup->id,
                ]);
            }
        }

        if(!empty($toRemove)) {
            ProductToModifierGroup::where('modifier_group_id', $modifierGroup->id)->whereIn('product_id', $toRemove)->delete();
        }

        return redirect()->route('modifier.group.index')->with('toast',[
            'title' => trans('public.modifier_group_updated'),
            'message' => trans('public.modifier_group_updated_caption'). $request->group_name,
            'type' => 'success'
        ]);
    }

    public function destroyGroup(Request $request)
    {
        $group = ModifierGroup::find($request->id);
        $name = $group->group_name;
        $group->delete();

        return redirect()->back()->with('toast', [
            'title' => trans('public.modifier_group_deleted'),
            'message' => trans('public.modifier_group_deleted_caption'). $name,
            'type' => 'success'
        ]);
    }

    public function indexItem()
    {
        $modifierItem = ModifierItem::select('updated_at')->orderByDesc('updated_at')->first();
        return Inertia::render('ModifierItem/ModifierItemList', [
            'modifierItem' => $modifierItem,
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
            'name' => json_encode($request->modifier_name),
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
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $fetchedItem = null;
            if($data['rows']) {
                $fetchedItem = $query->paginate($data['rows']);
                $fetchedItem->getCollection()->transform(function ($item) {
                    $item->group_count = $item->hasModifierGroupIds()->count();
                    return $item;
                });
            } else {
                $fetchedItem = $query->get();
            }
            return response()->json([
                'success' => true,
                'data' => $fetchedItem,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function updateItem(Request $request)
    {
        $rules = [
            'id' => ['required'],
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

        $item = ModifierItem::find($request->id);
        if(Str::slug($request->modifier_name['en']) !== $item->slug) {
            $item->slug = Str::slug($request->modifier_name['en']);
        }
        $item->name = json_encode($request->modifier_name);
        $item->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.modifier_item_updated'),
            'message' => trans('public.modifier_item_updated_caption'). $request->modifier_name[app()->getLocale()],
            'type' => 'success'
        ]);
    }

    public function destroyItem(Request $request)
    {
        $group = ModifierItem::find($request->id);
        $name = json_decode($group->name);
        $locale = app()->getLocale();
        $group->delete();

        return redirect()->back()->with('toast', [
            'title' => trans('public.modifier_group_deleted'),
            'message' => trans('public.modifier_group_deleted_caption'). $name->$locale,
            'type' => 'success'
        ]);
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
