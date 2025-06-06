<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;




class AdminAttendanceController extends Controller
{
    public function staffAttendance(Request $request, $user_id)
    {
        // 月の指定 or 今月
        $month = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startDate = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $currentMonth->copy()->endOfMonth()->format('Y-m-d');

        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];

        // attendancesをそのまま取得（break_timesのJOINは不要）
        $attendances = DB::table('attendances')
            ->where('user_id', $user_id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        // フォーマット処理
        $attendances->transform(function ($attendance) use ($dayOfWeekJP) {
            $workDate = Carbon::parse($attendance->work_date);
            $attendance->formatted_date = $workDate->format('m/d') . '（' . $dayOfWeekJP[$workDate->dayOfWeek] . '）';

            $clockIn = $attendance->clock_in ? Carbon::parse($attendance->clock_in) : null;
            $clockOut = $attendance->clock_out ? Carbon::parse($attendance->clock_out) : null;

            if ($clockIn && $clockOut && $clockOut->lt($clockIn)) {
                $clockOut->addDay();
            }

            $totalBreakMinutes = $attendance->total_break_minutes ?? 0;
            $workMinutes = $attendance->total_work_minutes ?? 0;

            $attendance->formatted_clock_in = $clockIn ? $clockIn->format('H:i') : '-';
            $attendance->formatted_clock_out = $clockOut ? $clockOut->format('H:i') : '-';
            $attendance->formatted_break = sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);
            $attendance->formatted_work = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);

            return $attendance;
        });

        $user = User::findOrFail($user_id);

        return view('admin.attendance.staff_attendance', compact(
            'attendances',
            'user',
            'month',
            'currentMonth',
            'prevMonth',
            'nextMonth'
        ));
    }





    public function list(Request $request,)
    {
        $date = $request->input('day', date('Y-m-d'));
        $parsedDate = Carbon::parse($date);
        $currentDay = $parsedDate->format('Y/m/d');

        $attendances = Attendance::with(['breaks', 'user'])
            ->whereDate('work_date', $parsedDate)
            ->orderBy('user_id')
            ->get();
        $users = $attendances->pluck('user', 'user_id');
        return view('admin.attendance.list', compact('attendances', 'currentDay', 'date'));
    }

    public function detail($attendance_id)
    {

        $attendance = Attendance::with(['breaks', 'user'])->findOrFail($attendance_id);

        return view('attendance.detail', ['attendances' => [$attendance]]);
    }

}

