@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_list.css') }}">
@endsection

@section('content')
<h1><span class="bar">｜</span> 申請一覧</h1>

<div class="month-navigation">
    <span class="nav-link">承認待ち</span>
    <span class="nav-link">承認済み</span>
</div>

<table class="attendance-table">
    <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach($corrections as $correction)
        <tr>
            <td class="gray">
                @if($correction->status === 'pending')
                承認待ち
                @elseif($correction->status === 'approved')
                承認済み
                @else
                その他
                @endif
            </td>

            <td class="gray">
                {{ $correction->attendance->user->name ?? '不明' }}
            </td>

            <td class="gray">
                {{ \Carbon\Carbon::parse($correction->attendance->work_date)->format('Y-m-d') }}
            </td>

            <td class="gray">
                {{ $correction->note ?? '（記載なし）' }}
            </td>

            <td class="gray">
                {{ \Carbon\Carbon::parse($correction->created_at)->format('Y-m-d H:i') }}
            </td>

            <td class="detail">
                <a href="{{ $isAdmin
                    ? route('admin.attendance.detail', $correction->attendance->id)
                    : route('user.attendance.detail', $correction->attendance->id) }}">
                    詳細
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection