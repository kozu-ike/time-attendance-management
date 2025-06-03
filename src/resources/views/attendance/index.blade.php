@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_index.css') }}">
@endsection

@section('content')
{{-- ステータス表示 --}}
<p class="status"><strong>{{ $status }}</strong></p>
<p class="date">{{ now()->format('Y年m月d日（D）') }}</p>
<p class="time">{{ now()->format('H:i') }}</p>

@if(session('message'))
<p style="color: green;">{{ session('message') }}</p>
@endif

<form action="{{ route('user.attendance.stamp') }}" method="POST">
    @csrf

    @if ($status === '勤務外')
    <button type="submit" name="action" value="start">出勤</button>
    @elseif ($status === '出勤中')
    <button type="submit" name="action" value="end">退勤</button>
    <button type="submit" name="action" value="break_start">休憩入り</button>
    @elseif ($status === '休憩中')
    <button type="submit" name="action" value="break_end">休憩戻り</button>
    @elseif ($status === '退勤済')
    <p>お疲れ様でした。</p>
    @endif

</form>
@endsection