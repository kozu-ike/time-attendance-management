@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<h1><span class="bar"></span><span class="current-day">{{ $currentDay }}</span>の勤怠</h1>

<div class="month-navigation">
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
        <tr>
            <td class="gray">{{ $attendance->user->name }}</td>
            <td class="gray">{{ $attendance->formatted_clock_in }}</td>
            <td class="gray">{{ $attendance->formatted_clock_out }}</td>
            <td class="gray">{{ $attendance->formatted_break }}</td>
            <td class="gray">{{ $attendance->formatted_work }}</td>
            <td class="detail">
                <a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection