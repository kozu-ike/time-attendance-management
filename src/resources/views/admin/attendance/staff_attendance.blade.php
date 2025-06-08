@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<h1><span class="bar">｜</span> {{ $user->name }}さんの勤怠</h1>

@php
use Carbon\Carbon;

$currentMonth = Carbon::parse($month ?? now());
$prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
$nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
@endphp


<div class="month-navigation">
    <a href="{{ route('admin.attendance.staff', ['user_id' => $user->id, 'month' => $prevMonth]) }}" class="nav-link">← 前月</a>

    <form method="GET" action="{{ route('admin.attendance.staff', ['user_id' => $user->id]) }}" class="month-form">
        <label for="month-picker" class="calendar-icon" title="月を選択">📅</label>
        <input type="month" id="month-picker" name="month" value="{{ $month ?? now()->format('Y-m') }}" style="display:none;" onchange="this.form.submit()">
        <span class="current-month">{{ $currentMonth->format('Y/m') }}</span>
    </form>

    <a href="{{ route('admin.attendance.staff', ['user_id' => $user->id,'month' => $nextMonth]) }}" class="nav-link">翌月 →</a>
</div>

<table class="attendance-table">
    <thead>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendances as $attendance)
        <tr>
            <td class="gray">{{ $attendance->formatted_date }}</td>
            <td class="gray">{{ $attendance->formatted_clock_in }}</td>
            <td class="gray">{{ $attendance->formatted_clock_out }}</td>
            <td class="gray">{{ $attendance->formatted_break }}</td>
            <td class="gray">{{ $attendance->formatted_work }}</td>
            <td class="detail"><a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>

<form method="GET" action="{{ route('admin.attendance.staff.csv', ['user_id' => $user->id]) }}">
    <input type="hidden" name="month" value="{{ $month ?? now()->format('Y-m') }}">
    <button type="submit" class="csv-button">CSV出力</button>
</form>
@endsection