<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AdminAuthController extends Controller
{
    // 管理者ログイン画面表示
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    // 管理者ログイン処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // 管理者専用ガードで認証を試みる（config/auth.php で管理者ガードを設定している場合）
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/attendance/list');
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ])->withInput($request->only('email'));
    }

    // ログアウト
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
