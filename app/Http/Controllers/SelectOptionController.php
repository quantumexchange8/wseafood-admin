<?php

namespace App\Http\Controllers;

use App\Enums\VoucherType;
use App\Models\User;
use App\Models\UserVoucherRedemption;
use Illuminate\Http\Request;

class SelectOptionController extends Controller
{
    public function getMembers(Request $request)
    {
        $keyword = $request->get('keyword');

        $users = User::where(function ($query) use ($keyword) {
            $query->where('full_name', 'like', '%' . $keyword . '%')
                ->orWhere('email', 'like', '%' . $keyword . '%')
                ->orWhere('phone_number', 'like', '%' . $keyword . '%');
        })->get();

        return response()->json($users);
    }

    public function getUserVouchers(Request $request)
    {
        $vouchers = UserVoucherRedemption::with('voucher.media')
            ->where([
                'user_id' => $request->user_id,
                'status' => VoucherType::REDEEMED
            ])
            ->get();

        return response()->json($vouchers);
    }
}
