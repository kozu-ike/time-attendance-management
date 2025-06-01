<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Models\Admin;


class AdminAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('/admin/login');
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (Auth::attempt($validated)) {
            return redirect('/admin/attendance/list');
        }

        return back();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/admin/login');
    }
}
