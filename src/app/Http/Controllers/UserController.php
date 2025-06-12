<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function staffList(Request $request)
    {

        $users = User::orderBy('name')->get();
        $month = $request->input('month', now()->format('Y-m'));

        return view('admin.attendance.staff_list', compact('users', 'month'));
    }
}
