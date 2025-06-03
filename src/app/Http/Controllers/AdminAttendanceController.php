<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;


class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        // 日付の取得（指定がなければ今日）
        $date = $request->input('day', date('Y-m-d'));
        $parsedDate = Carbon::parse($date);
        $currentDay = $parsedDate->format('Y/m/d');

        // 指定日の勤怠データ取得（ユーザー情報も含める）
        $attendances = Attendance::with(['breaks', 'user'])
            ->whereDate('work_date', $parsedDate)
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', compact('attendances', 'currentDay', 'date'));
    }
}
