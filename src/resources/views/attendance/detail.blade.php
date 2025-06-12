@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1><span class="bar"></span>勤怠詳細</h1>

    <form action="{{ route('attendance.update') }}" method="POST">
        @csrf
        @foreach($attendances as $attendance)
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
                    </td>
                </tr>
                <tr>
                    <th>出勤・退勤
                    </th>
                    <td>
                        <div class="time-inputs">
                            <input type="text" name="attendances[{{ $attendance->id }}][clock_in]"
                                value="{{ old('attendances.' . $attendance->id . '.clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}" />
                            　　　

                            <input type="text" name="attendances[{{ $attendance->id }}][clock_out]"
                                value="{{ old('attendances.' . $attendance->id . '.clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}" />
                        </div>
                        <div class="form__error">
                            @error("attendances.{$attendance->id}.clock_out")
                            {{ $message }}
                            @enderror
                        </div>

                    </td>
                </tr>
                @for ($i = 0; $i < $attendance->totalBreakCount; $i++)
                    @php
                    $breakIn=old("attendances.{$attendance->id}.breaks.{$i}.break_in") ?? ($breaks->get($i)->break_in ?? '');
                    $breakOut = old("attendances.{$attendance->id}.breaks.{$i}.break_out") ?? ($breaks->get($i)->break_out ?? '');

                    if ($breakIn) $breakIn = \Carbon\Carbon::parse($breakIn)->format('H:i');
                    if ($breakOut) $breakOut = \Carbon\Carbon::parse($breakOut)->format('H:i');
                    @endphp

                    @if ($i === 0)
                    <tr>
                        <th>休憩</th>
                        <td>

                            <div class="break-time">
                                <input type="text" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_in]" value="{{ $breakIn }}" placeholder="休憩開始" />

                                　〜　

                                <input type="text" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_out]" value="{{ $breakOut }}" placeholder="休憩終了" />
                            </div>
                            <div class="form__error">
                                @error("attendances.{$attendance->id}.breaks.{$i}.break_out")
                                {{ $message }}
                                @enderror
                            </div>

                        </td>
                    </tr>
                    @elseif ($i === 1)
                    <tr>
                        <th>休憩２</th>
                        <td>
                            <div class="break-time break-time-second">
                                <input type="text" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_in]" value="{{ $breakIn }}" placeholder="休憩開始" />
                                <div class="form__error">
                                    @error("attendances.{$attendance->id}.breaks.{$i}.break_in")
                                    {{ $message }}
                                    @enderror
                                </div>
                                　〜　
                                <input type="text" name="attendances[{{ $attendance->id }}][breaks][{{ $i }}][break_out]" value="{{ $breakOut }}" placeholder="休憩終了" />
                                <div class="form__error">
                                    @error("attendances.{$attendance->id}.breaks.{$i}.break_out")
                                    {{ $message }}
                                    @enderror
                                </div>
                            </div>
                            @endif
                            @endfor
                        </td>
                    </tr>


                    <tr>
                        <th>備考</th>
                        <td>
                            <input type="text" name="attendances[{{ $attendance->id }}][remarks]"
                                value="{{ old('attendances.' . $attendance->id . '.remarks', $attendance->remarks ?? '') }}"
                                placeholder="備考を入力" />
                            <div class="form__error">
                                @error("attendances.{$attendance->id}.remarks")
                                {{ $message }}
                                @enderror
                            </div>
                        </td>
                    </tr>
            </tbody>
        </table>
        @endforeach
        @if (isset($correction) && $correction->status === 'pending')
        <p class="message-pending">※承認待ちのため、修正はできません。</p>
        @else
        <button type="submit" class="btn-attendance-submit">修正</button>
        @endif
    </form>
</div>
@endsection