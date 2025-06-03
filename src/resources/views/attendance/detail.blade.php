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

<form method="POST" action="{{ route('user.attendance.update') }}">
    @csrf
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
            @foreach($attendances as $attendance)
            @php
            // 休憩時間は配列で扱う（break_in, break_outのペア）
            $breaks = $attendance->breaks ?? collect();

            // 休憩フィールドは既存休憩数 + 1分用意する
            $totalBreakCount = $breaks->count() + 1;
            @endphp
            <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>
                    <input type="date" name="attendances[{{ $attendance->id }}][work_date]"
                        value="{{ old('attendances.' . $attendance->id . '.work_date', $attendance->work_date) }}" />
                </td>
                <td>
                    <input type="time" name="attendances[{{ $attendance->id }}][clock_in]"
                        value="{{ old('attendances.' . $attendance->id . '.clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}" />
                </td>
                <td>
                    <input type="time" name="attendances[{{ $attendance->id }}][clock_out]"
                        value="{{ old('attendances.' . $attendance->id . '.clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}" />
                </td>

                {{-- 休憩フィールドを動的に複数表示 --}}
                @for ($i = 0; $i < $totalBreakCount; $i++)
                    @php
                    // old入力優先、なければDBの値を使う。存在しなければ空欄
                    $breakIn=old("attendances.{$attendance->id}.breaks.{$i}.break_in")
                    ?? ($breaks->get($i)->break_in ?? '');
                    $breakOut = old("attendances.{$attendance->id}.breaks.{$i}.break_out")
                    ?? ($breaks->get($i)->break_out ?? '');

                    // 表示用にHH:mm形式に整形（空欄ならそのまま）
                    if ($breakIn) $breakIn = \Carbon\Carbon::parse($breakIn)->format('H:i');
                    if ($breakOut) $breakOut = \Carbon\Carbon::parse($breakOut)->format('H:i');
                    @endphp

                    @if($i === 0)
                    <td>
                        <input type="time" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_in]" value="{{ $breakIn }}" placeholder="休憩開始" />
                        〜
                        <input type="time" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_out]" value="{{ $breakOut }}" placeholder="休憩終了" />
                    </td>
                    @elseif($i === 1)
                    <td>
                        <input type="time" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_in]" value="{{ $breakIn }}" placeholder="休憩開始" />
                        〜
                        <input type="time" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_out]" value="{{ $breakOut }}" placeholder="休憩終了" />
                    </td>
                    @endif
                    @endfor

                    @if ($totalBreakCount < 2)
                        {{-- 休憩2の列が空欄の場合 --}}
                        <td>
                        </td>
                        @endif

                        <td>
                            <input type="text" name="attendances[{{ $attendance->id }}][remarks]"
                                value="{{ old('attendances.' . $attendance->id . '.remarks', $attendance->remarks ?? '') }}"
                                placeholder="備考を入力" />
                        </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <button type="submit">修正</button>
</form>
@endsection