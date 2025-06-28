<?php

namespace App\Http\Controllers;

use App\Models\User;
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
}
