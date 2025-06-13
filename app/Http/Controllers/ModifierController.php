<?php

namespace App\Http\Controllers;

use App\Models\ModifierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

        return Inertia::render('ModifierGroup/CreateModifierGroup', [
            'itemCount' => $item_count,
        ]);
    }

    public function storeItem(Request $request)
    {
        $rules = [
            'modifier_name' => ['required', 'array'],
            'price' => ['required', 'numeric', 'min:0'],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["modifier_name.$locale"] = ['required'];
        }

        $attributeNames = [
            'modifier_name.*' => trans('public.modifier_name'),
            'price' => trans('public.price'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $item = ModifierItem::create([
            'modifier_name' => json_encode($request->modifier_name),
            'price' => $request->price,
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

            // $fetchedProduct = $query->paginate($data['rows']);
            $fetchedItem = $query->get();

            return response()->json([
                'success' => true,
                'data' => $fetchedItem,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
