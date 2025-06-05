<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;

use App\Models\StampCorrectionRequest;
use Carbon\Carbon;


class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('day', date('Y-m-d'));
        $parsedDate = Carbon::parse($date);
        $currentDay = $parsedDate->format('Y/m/d');

        $attendances = Attendance::with(['breaks', 'user'])
            ->whereDate('work_date', $parsedDate)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', compact('attendances', 'currentDay', 'date'));
    }


    public function staffAttendance(Request $request, $user_id)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user_id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        $user = User::findOrFail($user_id);

        return view('admin.attendance.staff', compact('attendances', 'user', 'month'));
    }
}

