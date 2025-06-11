<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Response;

class AdminAttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function staffAttendance(Request $request, $user_id)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $currentMonth = Carbon::parse($month);
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $startDate = $currentMonth->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $currentMonth->copy()->endOfMonth()->format('Y-m-d');

        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user_id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get()
            ->map(function ($item) {
                return $item;
            })
            ->map(function ($attendance) use ($dayOfWeekJP) {
                return $this->attendanceService->formatAttendance($attendance, $dayOfWeekJP);
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

    public function list(Request $request)
    {
        $date = $request->input('day', date('Y-m-d'));
        $parsedDate = Carbon::parse($date);
        $currentDay = $parsedDate->format('Y/m/d');

        $attendances = Attendance::with(['breaks', 'user'])
            ->whereDate('work_date', $parsedDate)
            ->orderBy('user_id')
            ->get()
            ->map(function ($attendance) {
                return $this->attendanceService->formatAttendance($attendance);
            });

        return view('admin.attendance.list', compact('attendances', 'currentDay', 'date'));
    }



    public function exportCsv(Request $request, $user_id)
    {
        $month = $request->query('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('user_id', $user_id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('work_date', 'asc')
            ->get()
            ->map(function ($attendance) {
                return $this->attendanceService->formatAttendance($attendance);
            });

        $columns = ['日付', '出勤', '退勤', '休憩時間', '勤務時間'];
        $columns_sjis = array_map(fn($col) => mb_convert_encoding($col, 'SJIS-win', 'UTF-8'), $columns);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=attendance_{$user_id}_{$month}.csv",
        ];

        $callback = function () use ($attendances, $columns_sjis) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns_sjis);

            foreach ($attendances as $attendance) {
                $row = [
                    $attendance->work_date,
                    $attendance->formatted_clock_in,
                    $attendance->formatted_clock_out,
                    $attendance->formatted_break,
                    $attendance->formatted_work,
                ];
                $row_sjis = array_map(fn($field) => mb_convert_encoding($field, 'SJIS-win', 'UTF-8'), $row);
                fputcsv($file, $row_sjis);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
