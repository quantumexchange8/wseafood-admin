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
            'amount' => ['required', 'numeric', 'min:1'],
            'point' => ['required', 'numeric', 'min:1'],
        ])->setAttributeNames([
            'user_id' => trans('public.member'),
            'method' => trans('public.method'),
            'amount' => trans('public.amount'),
            'point' => trans('public.point'),
        ])->validate();

        $user = User::find($request->user_id);
        $new_point = $user->point + $request->point;

        PointLog::create([
            'user_id' => $user->id,
            'type' => 'manage',
            'adjust_type' => $request->input('method'),
            'amount' => $request->amount,
            'earning_point' => $request->point,
            'old_point' => $user->point,
            'new_point' => $new_point,
            'remark' => $request->point == 'point_in' ? 'Receive' : 'Spend',
        ]);

        $user->point = $new_point;
        $user->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.point_updated'),
            'message' => trans('public.point_updated_caption'). $user->full_name,
            'type' => 'success'
        ]);
    }
}
