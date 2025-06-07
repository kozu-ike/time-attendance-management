<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\AttendanceService;




class AdminAttendanceController extends Controller
{
    public function staffAttendance(Request $request, $user_id)
    {
        $attendanceService = new AttendanceService();

        $month = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startDate = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $currentMonth->copy()->endOfMonth()->format('Y-m-d');

        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];

        // attendancesをEloquentではなくDBクエリで取得（breaks含まない）
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user_id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        // breaksをEloquentリレーションで取得するためUserモデルのattendancesに変更もあり
        // ここは簡略化のためにDBから取得したattendancesにbreaksは含まれていない点に注意

        // Eloquentに切り替え推奨
        // ここではbreaksを取得できていないため休憩計算はできない

        // ただし今回はサービスに渡せるように配列に変換し、breaksは空コレクションにセット
        $attendances = $attendances->map(function ($item) {
            $item->breaks = collect(); // 空コレクション
            return $item;
        });

        // フォーマット処理（breaksは空なので休憩は0分）
        $attendances = $attendances->map(function ($attendance) use ($attendanceService, $dayOfWeekJP) {
            return $attendanceService->formatAttendance($attendance, $dayOfWeekJP);
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

