@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1><span class="bar">｜</span>勤怠詳細</h1>

    @if ($errors->any())
    <div class="error-messages">
        <ul>
            @foreach ($errors->all() as $error)
            <li style="color:red;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @php
    $breaks = $attendance->breaks ?? collect();
    @endphp

    <table class="attendance-table">
        <tbody>
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <span class="date-year">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                    <span class="date-monthday">{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                </td>
            </tr>
            <tr>
                <th>出勤</th>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
            </tr>
            <tr>
                <th>退勤</th>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    @if ($breaks->get(0))
                    {{ \Carbon\Carbon::parse($breaks[0]->break_in)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($breaks[0]->break_out)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>
            </tr>
            <tr>
                <th>休憩２</th>
                <td>
                    @if ($breaks->get(1))
                    {{ \Carbon\Carbon::parse($breaks[1]->break_in)->format('H:i') }} 〜 {{ \Carbon\Carbon::parse($breaks[1]->break_out)->format('H:i') }}
                    @else
                    -
                    @endif
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>{{ $attendance->remarks ?? '（記載なし）' }}</td>
            </tr>
        </tbody>
    </table>

    @php
    $isAdmin = Auth::guard('admin')->check();
    @endphp

    
        @if ($isAdmin)
        @if ($correction->status === 'approved')
        <button type="button" class="btn-approved" disabled>承認済</button>
        @elseif ($correction->status === 'pending')
        <form method="POST" action="{{ route('stamp_correction_request.approve', $correction->id) }}">
            @csrf
            <button type="submit" class="btn-attendance-submit">承認</button>
        </form>
        @endif
        @else
        @if ($correction && $correction->status === 'pending')
        <p class="message-pending">※承認待ちのため、修正はできません。</p>

        @endif
        @endif
   
</div>
@endsection