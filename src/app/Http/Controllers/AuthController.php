<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use App\Mail\VerifyEmail;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        try {
            $this->sendVerificationEmail($user);
            return redirect()->route('auth.register');
        } catch (\Exception $e) {
            Log::error("メール送信に失敗しました: " . $e->getMessage());
        }

        return redirect()->route('verification.notice');
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        if (Auth::attempt($validated)) {
            $request->session()->regenerate();

            return redirect('/attendance');
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません。']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function sendVerificationEmail(User $user)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
    }

    public function verify()
    {
        return view('auth.verify');
    }

    public function verifyEmail($id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'このリンクは無効です。');
        }

        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect('/attendance')->with('message', 'メール認証が完了しました');
    }

    public function resendVerification()
    {
        $user = Auth::user();

        if ($user && !$user->hasVerifiedEmail()) {
            $this->sendVerificationEmail($user);
            return back()->with('resent', true);
        }

        return redirect('/attendance')->with('message', 'すでにメールが認証されています');
    }
}