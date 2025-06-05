@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<h1><span class="bar">｜</span> スタッフ一覧</h1>

@php
use Carbon\Carbon;

$currentMonth = Carbon::parse($month ?? now());
$prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
$nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
@endphp


<div class="month-navigation">
    <a href="{{ route('user.attendance.list', ['month' => $prevMonth]) }}" class="nav-link">← 前月</a>
    <form method="GET" action="{{ route('user.attendance.list') }}" class="month-form">
        <label for="month-picker" class="calendar-icon" title="月を選択">📅</label>
        <input type="month" id="month-picker" name="month" value="{{ $month ?? now()->format('Y-m') }}" style="display:none;" onchange="this.form.submit()">
        <span class="current-month">{{ $currentMonth->format('Y/m') }}</span>
    </form>

    <a href="{{ route('user.attendance.list', ['month' => $nextMonth]) }}" class="nav-link">翌月 →</a>
</div>

<table class="attendance-table">
    <thead>
        <tr>
            <th>名前</th>
            <th>メールアドレス</th>

            <th>月次勤怠</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td class="gray">{{ $user->name }}</td>
            <td class="gray">{{ $user->email }}</td>
            <td class="detail"><a href="{{ route('admin.attendance.staff', ['user' => $user->id, 'month' => $month ?? now()->format('Y-m')]) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection