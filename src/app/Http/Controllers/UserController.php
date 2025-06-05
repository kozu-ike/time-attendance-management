<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function staffList(Request $request)
    {
        $users = User::orderBy('name')->get();
        $month = $request->input('month', now()->format('Y-m'));

        return view('admin.staff.list', compact('users', 'month'));
    }
}
