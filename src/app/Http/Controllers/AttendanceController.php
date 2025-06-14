<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $user = Auth::user();
        $today = now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

        $status = '勤務外';
        if ($attendance) {
            if ($attendance->clock_out) {
                $status = '退勤済';
            } else {
                $lastBreak = $attendance->breaks()->latest('id')->first();
                if ($lastBreak && !$lastBreak->break_out) {
                    $status = '休憩中';
                } elseif ($attendance->clock_in) {
                    $status = '出勤中';
                }
            }
        }

        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];
        $formattedDate = $today->format('Y年m月d日') . "（{$weekday}）";

        return view('attendance.index', compact('status', 'formattedDate'));
    }

    public function stamp(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $action = $request->input('action');

        $attendance = Attendance::firstOrCreate([
            'user_id' => $user->id,
            'work_date' => $today,
        ]);

        switch ($action) {
            case 'start':
                if (!$attendance->clock_in) {
                    $attendance->clock_in = now();
                    $attendance->save();
                }
                break;
            case 'end':
                if ($attendance->clock_in && !$attendance->clock_out) {
                    $lastBreak = $attendance->breaks()->latest('id')->first();
                    if (!$lastBreak || $lastBreak->break_out) {
                        $attendance->clock_out = now();
                        $attendance->save();
                        session()->flash('just_clocked_out', true);
                    }
                }
                break;
            case 'break_start':
                if ($attendance->clock_in && !$attendance->clock_out) {
                    $lastBreak = $attendance->breaks()->latest('id')->first();
                    if (!$lastBreak || $lastBreak->break_out) {
                        $attendance->breaks()->create(['break_in' => now()]);
                    }
                }
                break;
            case 'break_end':
                $lastBreak = $attendance->breaks()->latest('id')->first();
                if ($lastBreak && !$lastBreak->break_out) {
                    $lastBreak->break_out = now();
                    $lastBreak->save();
                }
                break;
        }

        return redirect()->route('user.attendance.index');
    }

    public function list(Request $request)
    {
        $userId = Auth::id();
        $month = $request->input('month', now()->format('Y-m'));
        $data = $this->prepareMonthlyData($userId, $month);

        return view('attendance.list', $data);
    }

    private function prepareMonthlyData($userId, $month)
    {
        $currentMonth = Carbon::parse($month);
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        $start = $currentMonth->copy()->startOfMonth();
        $end = $currentMonth->copy()->endOfMonth();

        $attendanceCollection = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('work_date')
            ->get();

        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];
        $attendanceByDate = [];

        foreach ($attendanceCollection as $attendance) {
            $formatted = $this->attendanceService->formatAttendance($attendance, $dayOfWeekJP);
            $attendanceByDate[$attendance->work_date] = $formatted;
        }

        $attendances = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            if (isset($attendanceByDate[$dateStr])) {
                $attendances[] = $attendanceByDate[$dateStr];
            } else {
                $attendances[] = (object) [
                    'formatted_date' => $date->format('m/d') . '（' . $dayOfWeekJP[$date->dayOfWeek] . '）',
                    'formatted_clock_in' => '',
                    'formatted_clock_out' => '',
                    'formatted_break' => '',
                    'formatted_work' => '',
                    'id' => null,
                ];
            }
        }

        return compact('attendances', 'month', 'currentMonth', 'prevMonth', 'nextMonth');
    }


    public function detail($attendance_id)
    {
        if (Auth::guard('admin')->check()) {
            $attendance = Attendance::with(['breaks', 'user'])->findOrFail($attendance_id);
            $attendance->totalBreakCount = $attendance->breaks->count() + 1;
            return view('attendance.detail', ['attendances' => [$attendance], 'correction' => null]);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $attendance = Attendance::with('breaks')
                ->where('id', $attendance_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $attendance->totalBreakCount = $attendance->breaks->count() + 1;
            $correction = StampCorrectionRequest::where('attendance_id', $attendance_id)->first();

            return view('attendance.detail', ['attendances' => [$attendance], 'correction' => $correction]);
        }

        abort(403);
    }

    public function update(AttendanceRequest $request)
    {
        $user = Auth::guard('admin')->user() ?? Auth::user();
        $isAdmin = Auth::guard('admin')->check();

        foreach ($request->input('attendances', []) as $attendanceId => $data) {
            $attendance = Attendance::find($attendanceId);

            if (!$attendance || (!$isAdmin && $attendance->user_id !== $user->id)) {
                continue;
            }

            $originalClockIn = $attendance->clock_in;
            $originalClockOut = $attendance->clock_out;

            $attendance->clock_in = !empty($data['clock_in']) ? Carbon::parse($attendance->work_date . ' ' . $data['clock_in']) : null;
            $attendance->clock_out = !empty($data['clock_out']) ? Carbon::parse($attendance->work_date . ' ' . $data['clock_out']) : null;
            $attendance->save();

            $attendance->breaks()->delete();
            foreach ($data['breaks'] ?? [] as $break) {
                if (!empty($break['break_in']) && !empty($break['break_out'])) {
                    $attendance->breaks()->create([
                        'break_in' => Carbon::parse($attendance->work_date . ' ' . $break['break_in']),
                        'break_out' => Carbon::parse($attendance->work_date . ' ' . $break['break_out']),
                    ]);
                }
            }

            StampCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'request_date' => now()->toDateString(),
                'original_clock_in' => $originalClockIn,
                'original_clock_out' => $originalClockOut,
                'requested_clock_in' => $attendance->clock_in,
                'requested_clock_out' => $attendance->clock_out,
                'requested_breaks_json' => json_encode($attendance->breaks),
                'note' => $data['remarks'] ?? '',
                'status' => $isAdmin ? 'approved' : 'pending',
            ]);
        }

        return redirect()->route('attendance.detail', $attendance->id);
    }
}
