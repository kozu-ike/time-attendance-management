@extends('layouts.app')

@section('content')
<h1>勤怠登録</h1>

<p>{{ now()->format('Y年m月d日（D）') }}</p>
<p>{{ now()->format('H:i') }}</p>

{{-- ステータス表示 --}}
<p>現在のステータス: <strong>{{ $status }}</strong></p>

@if(session('message'))
<p style="color: green;">{{ session('message') }}</p>
@endif

<form action="{{ route('attendance.stamp') }}" method="POST">
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