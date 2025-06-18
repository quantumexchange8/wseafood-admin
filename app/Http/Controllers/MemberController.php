<?php

namespace App\Http\Controllers;

use App\Models\PointLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class MemberController extends Controller
{
    public function index()
    {
        $member = User::select('updated_at')->orderByDesc('updated_at')->first();
        $memberCount = User::all()->count();

        return Inertia::render('Member/MemberList', [
            'member' => $member,
            'mmeberCount' => $memberCount,
        ]);
    }

    public function fetchMember(Request $request)
    {
        if ($request->has('lazyEvent')) {
            $data = json_decode($request->only(['lazyEvent'])['lazyEvent'], true);

            $query = User::query();

            if ($data['filters']['global']['value']) {
                $keyword = $data['filters']['global']['value'];

                $query->where(function ($q) use ($keyword) {
                    $q->where('full_name', 'like', '%' . $keyword . '%');
                });
            }

            if ($data['filters']['status']['value']) {
                $query->where('status', $data['filters']['status']['value']);
            }

            if ($data['filters']['pointRange']['value']) {
                $range = $data['filters']['pointRange']['value'];
                $query->whereBetween('point', [$range[0], $range[1]]);
            }

            if ($data['filters']['date']['value']) {
                $range = $data['filters']['date']['value'];
                $query->whereBetween('created_at', [$range[0], $range[1]]);
            }

            if ($data['sortField'] && $data['sortOrder']) {
                $order = $data['sortOrder'] == 1 ? 'asc' : 'desc';
                $query->orderBy($data['sortField'], $order);
            } else {
                $query->orderByDesc('created_at');
            }

            $fetchedMember = $query->paginate($data['rows']);

            $fetchedMember->getCollection()->transform(function ($member) {
                $member->profile_photo = $member->getFirstMediaUrl('profile_photo');
                return $member;
            });

            return response()->json([
                'success' => true,
                'data' => $fetchedMember,
            ]);
        }

        return response()->json(['success' => false, 'data' => []]);
    }

    public function adjustPoint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'adjustPoint' => ['required', 'numeric'],
            'remark' => ['nullable', 'sometimes', 'string']
        ])->setAttributeNames([
            'adjustPoint' => trans('public.adjust_point'),
            'remark' => trans('public.remark'),
        ]);
        $validator->validate();

        $member = User::find($request->memberId);
        $newPoint = $member->point + $request->adjustPoint;

        if($newPoint < 0) {
            throw ValidationException::withMessages(['adjustPoint' => trans('public.insufficient_point')]);
        }

        $type = $request->adjustPoint > 0 ? 'deposit' : 'withdrawal';

        PointLog::create([
            'user_id' => $member->id,
            'type' => 'adjustment',
            'adjust_type' => $type,
            'amount' => $request->adjustPoint,
            'earning_point' => $request->adjustPoint,
            'old_point' => $member->point,
            'new_point' => $newPoint,
            'remark' => $request->remark,
        ]);

        $member->point = $newPoint;
        $member->save();

        return redirect()->back()->with('toast', [
            'title' => trans('public.point_updated'),
            'message' => trans('public.point_updated_caption'). $member->full_name,
            'type' => 'success'
        ]);
    }

    public function destroy(Request $request)
    {
        $member = User::find($request->id);
        $name = $member->full_name;
        $member->delete();

        return redirect()->back()->with('toast', [
            'title' => trans('public.member_deleted'),
            'message' => trans('public.member_deleted_caption'). $name,
            'type' => 'success'
        ]);
    }
}
