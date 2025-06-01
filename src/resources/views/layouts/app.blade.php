<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>time-attendance-management</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-logo">
            <a href="{{ url('/') }}">
                <img src="/images/logo.svg" alt="ロゴ">
            </a>
        </div>

        @php
        $user = Auth::user();
        $routeName = Route::currentRouteName();
        $justClockedOut = session()->pull('just_clocked_out');
        @endphp

        {{-- ログイン・登録・メール認証はロゴだけ --}}
        @if (!in_array($routeName, ['login', 'register', 'verification.notice']))
        <nav class="header-nav">
            @if ($user && $user->is_admin)
            {{-- 管理者メニュー --}}
            <a href="{{ url('/admin/attendance/list') }}">勤怠一覧</a>
            <a href="{{ url('/admin/staff/list') }}">スタッフ一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>
            <form action="/logout" method="post" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
            @elseif ($user)
            @if ($justClockedOut)
            {{-- 退勤直後メニュー --}}
            <a href="{{ url('/attendance/list') }}">今月の出勤一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>
            <form action="/logout" method="post" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
            @else
            {{-- 通常メニュー --}}
            <a href="{{ url('/attendance') }}">勤怠</a>
            <a href="{{ url('/attendance/list') }}">勤怠一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請</a>
            <form action="/logout" method="post" style="display:inline;">
                @csrf
                <button type="submit">ログアウト</button>
            </form>
            @endif
            @endif
        </nav>
        @endif
    </header>

    {{-- お疲れ様メッセージ --}}
    @if ($justClockedOut)
    <div class="flash-message">
        お疲れ様でした！
    </div>
    @endif

    <main>
        @yield('content')
    </main>
</body>

</html>