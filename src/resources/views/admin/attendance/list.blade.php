@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<h1><span class="bar">｜</span> <span class="current-day">{{ $currentDay }}</span>の勤怠</h1>

@php
use Carbon\Carbon;

$currentDate = Carbon::parse($date);
$prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
$nextDate = $currentDate->copy()->addDay()->format('Y-m-d');
@endphp

<div class="day-navigation">
    <a href="{{ route('admin.attendance.list', ['day' => $prevDate]) }}" class="nav-link">← 前日</a>

    {{-- 日付選択 --}}
    <form method="GET" action="{{ route('admin.attendance.list') }}" class="day-form">
        <label for="day-picker" class="calendar-icon" title="日を選択">📅</label>
        <input type="date" id="day-picker" name="day" value="{{ $date ?? now()->format('Y-m') }}" style="display:none;" onchange=" this.form.submit()">
        <span class="current-day">{{ $currentDay }}</span>
    </form>

    {{-- 翌日リンク --}}
    <a href="{{ route('admin.attendance.list', ['day' => $nextDate]) }}" class="nav-link">翌日 →</a>

</div>

<table class="attendance-table">
    <thead>
        <tr>
            <th>名前</th>
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
            <td class="gray">{{ $attendance->user->name }}</td>
            <td class="gray">{{ $clockIn ? $clockIn->format('H:i') : '-' }}</td>
            <td class="gray">{{ $clockOut ? $clockOut->format('H:i') : '-' }}</td>
            <td class="gray">{{ $breakHours }}:{{ $breakMinutes }}</td>
            <td class="gray">{{ $workHours }}:{{ $workRemainMinutes }}</td>
            <td class="detail">
                <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection