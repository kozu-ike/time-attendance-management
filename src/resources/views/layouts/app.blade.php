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
        use Illuminate\Support\Facades\Route;
        use Illuminate\Support\Facades\Auth;

        $routeName = Route::currentRouteName();


        $justClockedOut = session()->get('just_clocked_out');
        @endphp

        @if (!in_array($routeName, ['login', 'register', 'verification.notice']))
        <nav class="header-nav">
            @if(Auth::guard('admin')->check())
            <a href="{{ url('/admin/attendance/list') }}">勤怠一覧</a>
            <a href="{{ url('admin/attendance/staff_list') }}">スタッフ一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>
            <form action="/logout" method="post" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout">ログアウト</button>
            </form>
            @elseif (Auth::check())
            @if ($justClockedOut)
            <a href="{{ url('/attendance/list') }}">今月の出勤一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>
            <form action="/logout" method="post" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout">ログアウト</button>
            </form>
            @else
            <a href="{{ url('/attendance') }}">勤怠</a>
            <a href="{{ url('/attendance/list') }}">勤怠一覧</a>
            <a href="{{ url('/stamp_correction_request/list') }}">申請</a>
            <form action="/logout" method="post" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout">ログアウト</button>


            </form>
            @endif
            @endif
        </nav>
        @endif
    </header>

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