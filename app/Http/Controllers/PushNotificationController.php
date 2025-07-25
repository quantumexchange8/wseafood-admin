<?php

namespace App\Http\Controllers;

use App\Jobs\PushNotificationJob;
use App\Models\PushNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class PushNotificationController extends Controller
{
    public function index()
    {
        $notification = PushNotification::select('updated_at')->orderByDesc('updated_at')->first();
        $notificationCount = PushNotification::all()->count();

        return Inertia::render('PushNotification/PushNotificationList', [
            'notification' => $notification,
            'notificationCount' => $notificationCount,
        ]);
    }

    public function create()
    {
        return Inertia::render('PushNotification/CreateNotification');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => ['required', 'array'],
            'message' => ['required', 'array'],
            'content' => ['required'],
            'schedule_type' => ['required'],
            'schedule_datetime' => ["required_if:schedule_type,scheduled"],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["title.$locale"] = ['required'];
            $rules["message.$locale"] = ['required'];
        }

        $attributeNames = [
            'title.*' => trans('public.title'),
            'message.*' => trans('public.message'),
            'content' => trans('public.content'),
            'schedule_type' => trans('public.schedule_type'),
            'schedule_datetime' => trans('public.schedule_time'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $push_notification = PushNotification::create([
            'title' => json_encode($request->title),
            'message' => json_encode($request->message),
            'content' => $request->input('content'),
            'schedule_type' => $request->schedule_type,
            'schedule_datetime' => $request->schedule_datetime
                ? Carbon::parse($request->schedule_datetime)->seconds(0)->format('Y-m-d H:i:s')
                : null,
            'status' => 'active',
        ]);

        if ($request->push_now) {
            dispatch(new PushNotificationJob($push_notification));
        }

        return redirect()->route('notification.index')->with('toast', [
            'title' => trans('public.notification_created'),
            'message' => trans('public.notification_created_caption'). $request->title[app()->getLocale()],
            'type' => 'success'
        ]);
    }

    public function fetchNotification(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = PushNotification::query();

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['filters']['status']['value']) {
                $query->where('status', $data['filters']['status']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $fetchedNotifications = $query->paginate($data['rows']);

            return response()->json([
                'success' => true,
                'data' => $fetchedNotifications,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function updateStatus(Request $request)
    {
        $category = PushNotification::find($request->id);

        if($request->status === 'active') {
            $category->status = 'inactive';
        } else {
            $category->status = 'active';
        }
        $category->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.status_updated'),
            'message' => trans('public.status_updated_caption'). $request->name,
            'type' => 'success'
        ]);
    }

    public function edit(Request $request)
    {
        $notification = PushNotification::find($request->id);
        $notification->title = json_decode($notification->title);
        $notification->message = json_decode($notification->message);

        return Inertia::render('PushNotification/EditNotification', [
            'notification' => $notification,
        ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'title' => ['required', 'array'],
            'message' => ['required', 'array'],
            'content' => ['required'],
            'schedule_type' => ['required'],
            'schedule_datetime' => ["required_if:schedule_type,scheduled"],
        ];

        foreach(config('app.available_locales') as $locale) {
            $rules["title.$locale"] = ['required'];
            $rules["message.$locale"] = ['required'];
        }

        $attributeNames = [
            'title.*' => trans('public.title'),
            'message.*' => trans('public.message'),
            'content' => trans('public.content'),
            'schedule_type' => trans('public.schedule_type'),
            'schedule_datetime' => trans('public.schedule_time'),
        ];

        $validator = Validator::make($request->all(), $rules)->setAttributeNames($attributeNames);
        $validator->validate();

        $notification = PushNotification::find($request->id);

        $notification->update([
            'title' => json_encode($request->title),
            'message' => json_encode($request->message),
            'content' => $request->input('content'),
            'schedule_type' => $request->schedule_type,
            'schedule_datetime' => $request->schedule_datetime,
            'status' => 'active',
        ]);

        if ($request->push_now) {
            dispatch(new PushNotificationJob($notification));
        }

        return redirect()->route('notification.index')->with('toast', [
            'title' => trans('public.notification_updated'),
            'message' => trans('public.notification_updated_caption'). $request->title[app()->getLocale()],
            'type' => 'success'
        ]);
    }

    public function destroy(Request $request)
    {
        $notification = PushNotification::find($request->id);
        $name = json_decode($notification->title);
        $locale = app()->getLocale();
        $notification->delete();

        return redirect()->back()->with('toast', [
            'title' => trans('public.notification_deleted'),
            'message' => trans('public.notification_deleted_caption'). $name->$locale,
            'type' => 'success'
        ]);
    }

    public function push_notification($id)
    {
        $notification = PushNotification::find($id);

        if ($notification) {
            dispatch(new PushNotificationJob($notification));

            return redirect()->back()->with('toast', [
                'title' => trans('public.push_notification'),
                'message' => trans('public.successfully_push_notification'),
                'type' => 'success'
            ]);
        } else {
            return redirect()->back()->with('toast', [
                'title' => trans('public.error'),
                'message' => trans('public.notification_not_found'),
                'type' => 'error'
            ]);
        }
    }
}
