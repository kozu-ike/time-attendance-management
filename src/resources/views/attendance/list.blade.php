@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1><span class="bar"></span> 勤怠一覧</h1>


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

            <tr>
                <td>{{ $attendance->formatted_date }}</td>
                <td>{{ $attendance->formatted_clock_in }}</td>
                <td>{{ $attendance->formatted_clock_out }}</td>
                <td>{{ $attendance->formatted_break }}</td>
                <td>{{ $attendance->formatted_work }}</td>
                <td class="detail">
                    @if($attendance->id)
                    <a href="{{ route('attendance.detail', $attendance->id) }}">詳細</a>
                    @else
                    詳細
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <
    @endsection