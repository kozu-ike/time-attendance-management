<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;


class AdminAttendanceController extends Controller
{
    


    public function staffAttendance(Request $request, $user_id)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $currentMonth = \Carbon\Carbon::parse($month);
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user_id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

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

