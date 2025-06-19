<?php

namespace App\Http\Controllers;

use App\Models\Highlight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class HighlightController extends Controller
{
    public function index()
    {
        $highlight = Highlight::select('updated_at')->orderByDesc('updated_at')->first();
        $highlightCount = Highlight::all()->count();

        return Inertia::render('Highlight/HighlightList', [
            'highlight' => $highlight,
            'highlightCount' => $highlightCount,
        ]);
    }

    public function create()
    {
        return Inertia::render('Highlight/CreateHighlight');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            // 'content' => ['required'],
            'popup' => ['required'],
            'status' => ['required'],
            'highlight_photo' => ['required'],
        ];

        $attributeNames = [
            'title' => trans('public.title'),
            // 'content' => trans('public.content'),
            'popup' => trans('public.popup_highlight'),
            'status' => trans('public.visibility'),
            'highlight_photo' => trans('public.highlight_photo'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $hightlightClass = new Highlight();
        $hightlight = Highlight::create([
            'title' => $request->title,
            'content' => 'no content',
            'status' => $request->status,
            'position' => $hightlightClass->getPosition(),
            'can_popup' => $request->popup,
            'url' => 'test link',
        ]);

        if($request->hasFile('highlight_photo')) {
            $hightlight->addMedia($request->highlight_photo)->toMediaCollection('highlight_photo');
        }

        return redirect()->route('highlight.index')->with('toast', [
            'title' => trans('public.highlight_created'),
            'message' => trans('public.highlight_created_caption'). $request->title,
            'type' => 'success'
        ]);
    }

    public function fetchHighlight(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = Highlight::query();

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['filters']['status']['value']) {
                $query->where('status', $data['filters']['status']['value']);
            }

            if ($data['filters']['popup']['value'] !== null) {
                $query->where('can_popup', $data['filters']['popup']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderBy('position');
            }

            $fetchedHighlights = $query->paginate($data['rows']);

            $fetchedHighlights->getCollection()->transform(function ($highlight) {
                $highlight->highlight_photo = $highlight->getFirstMediaUrl('highlight_photo');
                return $highlight;
            });

            return response()->json([
                'success' => true,
                'data' => $fetchedHighlights,
            ]);
        }
    }

    public function reorder(Request $request)
    {
        $highlights = $request->all();
        foreach($highlights as $key => $highlight) {
            $findHighlight = Highlight::find($highlight['id']);
            $findHighlight->position = $key + 1;
            $findHighlight->save();
        }

        return redirect()->back()->with('toast', [
            'title' => trans('public.position_updated'),
            'message' => trans('public.position_updated_caption'),
            'type' => 'success'
        ]);
    }

    public function updateStatus(Request $request)
    {
        $highlight = Highlight::find($request->id);

        if($request->status === 'active') {
            $highlight->status = 'inactive';
        } else {
            $highlight->status = 'active';
        }
        $highlight->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.status_updated'),
            'message' => trans('public.status_updated_caption'). $request->name,
            'type' => 'success'
        ]);
    }

    public function updatePopup(Request $request)
    {
        $highlight = Highlight::find($request->id);

        if($request->popup === 1) {
            $highlight->can_popup = 0;
        } else {
            $highlight->can_popup = 1;
        }
        $highlight->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.popup_updated'),
            'message' => trans('public.popup_updated_caption'). $request->name,
            'type' => 'success'
        ]);
    }

    public function destroy(Request $request)
    {
        $highlight = Highlight::find($request->id);
        $name = $highlight->title;
        $highlight->delete();

        return redirect()->back()->with('toast', [
            'title' => trans('public.highlight_deleted'),
            'message' => trans('public.highlight_deleted_caption'). $name,
            'type' => 'success'
        ]);
    }
}
