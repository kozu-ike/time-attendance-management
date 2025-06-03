@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<h1><span class="bar">｜</span> 勤怠一覧</h1>

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
        @php
        $clockIn = $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in) : null;
        $clockOut = $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out) : null;
        $totalBreakMinutes = $attendance->breaks->reduce(function($carry, $break) {
        if ($break->break_in && $break->break_out) {
        $in = \Carbon\Carbon::parse($break->break_in);
        $out = \Carbon\Carbon::parse($break->break_out);
        return $carry + $out->diffInMinutes($in);
        }
        return $carry;
        }, 0);
        $workMinutes = $clockIn && $clockOut ? $clockOut->diffInMinutes($clockIn) - $totalBreakMinutes : 0;
        $workHours = floor($workMinutes / 60);
        $workRemainMinutes = str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT);
        $breakHours = floor($totalBreakMinutes / 60);
        $breakMinutes = str_pad($totalBreakMinutes % 60, 2, '0', STR_PAD_LEFT);
        @endphp
        <tr>
            <td class="gray">{{ $attendance->work_date }}</td>
            <td class="gray">{{ $clockIn ? $clockIn->format('H:i') : '-' }}</td>
            <td class="gray">{{ $clockOut ? $clockOut->format('H:i') : '-' }}</td>
            <td class="gray">{{ $breakHours }}:{{ $breakMinutes }}</td>
            <td class="gray">{{ $workHours }}:{{ $workRemainMinutes }}</td>
            <td class="detail"><a href="{{ route('user.attendance.detail', $attendance->id) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection