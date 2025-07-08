<?php

namespace App\Http\Controllers;

use App\Models\PointLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class PointController extends Controller
{
    public function index()
    {
        $query = PointLog::query();

        return Inertia::render('Point/PointHistory', [
            'pointLogsCount' => $query->count(),
            'pointLogLastUpdatedAt' => $query->latest('updated_at')->first()->updated_at,
        ]);
    }

    public function manage_point(Request $request)
    {
        Validator::make($request->all(), [
            'user_id' => ['required'],
            'method' => ['required'],
            'point' => ['required', 'numeric', 'min:1'],
            'remark' => ['required']
        ])->setAttributeNames([
            'user_id' => trans('public.member'),
            'method' => trans('public.method'),
            'point' => trans('public.point'),
            'remark' => trans('public.remark'),
        ])->validate();

        $user = User::find($request->user_id);
        $new_point = $user->point + $request->point;

        PointLog::create([
            'user_id'      => $user->id,
            'type'         => 'manage',
            'adjust_type'  => $request->input('method'),
            'amount'       => $request->input('method') == 'point_out' ? -abs($request->point) : abs($request->point),
            'earning_point'=> $request->input('method') == 'point_out' ? -abs($request->point) : abs($request->point),
            'old_point'    => $user->point,
            'new_point'    => $new_point,
            'remark'       => $request->remark,
        ]);

        $user->point = $new_point;
        $user->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.point_updated'),
            'message' => trans('public.point_updated_caption'). $user->full_name,
            'type' => 'success'
        ]);
    }

    public function getPointHistoryData(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = PointLog::query()
                ->with([
                    'user',
                    'user.media'
                ]);

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('user', function ($q) use ($keyword) {
                        $q->where('full_name', 'like', "%$keyword%")
                            ->orWhere('email', 'like', "%$keyword%")
                            ->orWhere('phone_number', 'like', "%$keyword%");
                    });
                });
            }

            if ($data['filters']['type']['value']) {
                $query->where('adjust_type', $data['filters']['type']['value']);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->latest();
            }

            $point_histories = $query->paginate($data['rows']);

            $point_histories->getCollection()->transform(function ($point) {
                $point->user->profile_photo = $point->user->getFirstMediaUrl('profile_photo');
                return $point;
            });

            return response()->json([
                'success' => true,
                'data' => $point_histories,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }
}
