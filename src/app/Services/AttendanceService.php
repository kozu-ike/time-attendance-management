<?php

namespace App\Services;

use Carbon\Carbon;

class AttendanceService
{
    /**
     * 勤怠データを受け取り、
     * 勤務時間・休憩時間などを計算し
     * フォーマット済みのプロパティを追加します。
     *
     * @param object $attendance 勤怠データ（breaksリレーション含む）
     * @param array $dayOfWeekJP 曜日の日本語配列
     * @return object フォーマット済みの勤怠データ
     */
    public function formatAttendance($attendance, $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'])
    {
        $workDate = Carbon::parse($attendance->work_date);
        $attendance->formatted_date = $workDate->format('m/d') . '（' . $dayOfWeekJP[$workDate->dayOfWeek] . '）';

        $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
        $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

        if ($clockIn && $clockOut && $clockOut->lt($clockIn)) {
            $clockOut->addDay();
        }

        $totalBreakMinutes = $attendance->breaks->reduce(function ($carry, $break) {
            if (!$break->break_in || !$break->break_out) {
                return $carry; // break_inまたはbreak_outが無ければスキップ
            }

            $in = Carbon::parse($break->break_in);
            $out = Carbon::parse($break->break_out);

            if ($out->lt($in)) {
                $out->addDay();
            }

            $diff = $in->diffInMinutes($out);

            return $carry + $diff;
        }, 0);

        $workMinutesRaw = ($clockIn && $clockOut) ? $clockIn->diffInMinutes($clockOut) - $totalBreakMinutes : null;
        $workMinutes = is_null($workMinutesRaw) ? null : max(0, $workMinutesRaw);

        $formatMinutes = function ($minutes) {
            if (is_null($minutes)) {
                return '-';
            }
            $h = floor($minutes / 60);
            $m = $minutes % 60;
            return sprintf('%d:%02d', $h, $m);
        };

        $attendance->formatted_clock_in = $clockIn ? $clockIn->format('H:i') : '-';
        $attendance->formatted_clock_out = $clockOut ? $clockOut->format('H:i') : '-';
        $attendance->formatted_break = $formatMinutes($totalBreakMinutes);
        $attendance->formatted_work = $formatMinutes($workMinutes);

        return $attendance;
    }
}
