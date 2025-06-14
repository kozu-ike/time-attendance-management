@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<h1><span class="bar"></span> スタッフ一覧</h1>

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
            <td class="detail"><a href="{{ route('admin.attendance.staff', ['user_id' => $user->id, 'month' => $month ?? now()->format('Y-m')]) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection