@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<h1>勤怠詳細</h1>

@if ($errors->any())
<div class="error-messages">
    <ul>
        @foreach ($errors->all() as $error)
        <li style="color:red;">{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<table>
    <thead>
        <tr>
            <th>名前</th>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>休憩２</th>
            <th>備考</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $attendance->user->name }}</td>
            <td>{{ $attendance->work_date }}</td>
            <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
            <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
            @php
            $breaks = $attendance->breaks ?? collect();
            @endphp
            <td>
                @if ($breaks->get(0))
                {{ \Carbon\Carbon::parse($breaks[0]->break_in)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($breaks[0]->break_out)->format('H:i') }}
                @else
                -
                @endif
            </td>
            <td>
                @if ($breaks->get(1))
                {{ \Carbon\Carbon::parse($breaks[1]->break_in)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($breaks[1]->break_out)->format('H:i') }}
                @else
                -
                @endif
            </td>
            <td>{{ $attendance->remarks ?? '（記載なし）' }}</td>
        </tr>
    </tbody>
</table>

@php
$isAdmin = Auth::check() && Auth::user()->is_admin;
@endphp

<div style="margin-top: 20px;">
    @if ($isAdmin)
    @if ($correction->status === 'approved')
    <button type="button" disabled style="background-color: gray; color: white;">承認済</button>
    @elseif ($correction->status === 'pending')
    <form method="POST" action="{{ route('stamp_correction_request.approve', $correction->id) }}">
        @csrf
        <button type="submit" style="background-color: green; color: white;">承認</button>
    </form>
    @endif
    @else
    @if ($correction->status === 'pending')
    <p style="color: red;">※承認待ちのため、修正はできません。</p>
    @elseif ($correction->status === 'approved')
    <p style="color: green;">この勤怠は承認済みです。</p>
    @endif
    @endif
</div>
@endsection