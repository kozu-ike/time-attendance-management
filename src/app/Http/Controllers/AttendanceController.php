<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use App\Services\AttendanceService;

class AttendanceController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $today = now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

        if (!$attendance) {
            $status = '勤務外';
        } elseif ($attendance->clock_out) {
            $status = '退勤済';
        } else {
            $lastBreak = $attendance ? $attendance->breaks()->latest('id')->first() : null;

            if ($lastBreak && !$lastBreak->break_out) {
                $status = '休憩中';
            } elseif ($attendance->clock_in && !$attendance->clock_out) {
                $status = '出勤中';
            } else {
                $status = '勤務外';
            }
        }

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $weekday = $weekdays[$today->dayOfWeek];

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

        $message = null;

        switch ($action) {
            case 'start':
                if (!$attendance->clock_in) {
                    $attendance->clock_in = now();
                    $attendance->save();
                }
                break;

            case 'end':
                if ($attendance->clock_in && !$attendance->clock_out) {
                    $lastBreak = $attendance->breaks()->latest('id')->first(); //userIDじゃなくていいの？いまは最後のIDからもってきてるから修正がいるかも？？
                    if ($lastBreak && !$lastBreak->break_out) {
                        break;
                    }
                    $attendance->clock_out = now();
                    $attendance->save();
                    session()->flash('just_clocked_out', true);
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


            default:
                break;
        }

        return redirect()->route('user.attendance.index')->with('message', $message);
    }



    public function list(Request $request)
    {
        $attendanceService = new AttendanceService();

        $month = $request->input('month', date('Y-m'));
        $userId = Auth::id();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereYear('work_date', '=', substr($month, 0, 4))
            ->whereMonth('work_date', '=', substr($month, 5, 2))
            ->orderBy('work_date', 'desc')
            ->get();

        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];


        $attendances = $attendances->map(function ($attendance) use ($attendanceService, $dayOfWeekJP) {
            return $attendanceService->formatAttendance($attendance, $dayOfWeekJP);
        });

        $currentMonth = Carbon::parse($month)->format('Y年m月');

        return view('attendance.list', compact('attendances', 'month', 'currentMonth'));
    }


    public function detail($attendance_id)
    {
        if (Auth::guard('admin')->check()) {
            $attendance = Attendance::with(['breaks', 'user'])->findOrFail($attendance_id);

            return view('attendance.detail', [
                'attendances' => [$attendance],
                'correction' => null,
            ]);
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::user();

            $attendance = Attendance::with('breaks')
                ->where('id', $attendance_id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $correction = StampCorrectionRequest::where('attendance_id', $attendance_id)->first();

            return view('attendance.detail', [
                'attendances' => [$attendance],
                'correction' => $correction,
            ]);
        }

        abort(403, 'Unauthorized');
    }


    public function update(AttendanceRequest $request)
    {
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $attendancesInput = $request->input('attendances', []);

            foreach ($attendancesInput as $attendanceId => $data) {
                $attendance = Attendance::find($attendanceId);
                if (!$attendance) continue;

                $attendance->clock_in = !empty($data['clock_in'])
                    ? Carbon::parse($attendance->work_date . ' ' . $data['clock_in'])
                    : null;
                $attendance->clock_out = !empty($data['clock_out'])
                    ? Carbon::parse($attendance->work_date . ' ' . $data['clock_out'])
                    : null;
                $note = $data['remarks'] ?? '';
                $attendance->save();

                $attendance->breaks()->delete();
                if (isset($data['breaks']) && is_array($data['breaks'])) {
                    foreach ($data['breaks'] as $break) {
                        if (!empty($break['break_in']) && !empty($break['break_out'])) {
                            $workDate = $attendance->work_date;
                            $breakIn = Carbon::parse($workDate . ' ' . $break['break_in']);
                            $breakOut = Carbon::parse($workDate . ' ' . $break['break_out']);
                            $attendance->breaks()->create([
                                'break_in' => $breakIn,
                                'break_out' => $breakOut,
                            ]);
                        }
                    }
                }

                StampCorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $attendance->user_id,
                    'request_date' => now()->toDateString(),
                    'original_clock_in' => $attendance->getOriginal('clock_in'),
                    'original_clock_out' => $attendance->getOriginal('clock_out'),
                    'requested_clock_in' => $attendance->clock_in,
                    'requested_clock_out' => $attendance->clock_out,
                    'requested_breaks_json' => json_encode($attendance->breaks),
                    'note' => $note,
                    'status' => 'approved',
                ]);
            }

            return redirect()->route('attendance.detail', $attendance->id);
        }

        if (Auth::check()) {
            $user = Auth::user();
            $attendancesInput = $request->input('attendances', []);

            foreach ($attendancesInput as $attendanceId => $data) {
                $attendance = Attendance::where('id', $attendanceId)
                    ->where('user_id', $user->id)
                    ->first();

                if (!$attendance) continue;

                $attendance->clock_in = !empty($data['clock_in'])
                    ? Carbon::parse($attendance->work_date . ' ' . $data['clock_in'])
                    : null;
                $attendance->clock_out = !empty($data['clock_out'])
                    ? Carbon::parse($attendance->work_date . ' ' . $data['clock_out'])
                    : null;
                $note = $data['remarks'] ?? '';
                $attendance->save();

                $attendance->breaks()->delete();
                if (isset($data['breaks']) && is_array($data['breaks'])) {
                    foreach ($data['breaks'] as $break) {
                        if (!empty($break['break_in']) && !empty($break['break_out'])) {
                            $workDate = $attendance->work_date;
                            $breakIn = Carbon::parse($workDate . ' ' . $break['break_in']);
                            $breakOut = Carbon::parse($workDate . ' ' . $break['break_out']);
                            $attendance->breaks()->create([
                                'break_in' => $breakIn,
                                'break_out' => $breakOut,
                            ]);
                        }
                    }
                }

                StampCorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id' => $user->id,
                    'request_date' => now()->toDateString(),
                    'original_clock_in' => $attendance->getOriginal('clock_in'),
                    'original_clock_out' => $attendance->getOriginal('clock_out'),
                    'requested_clock_in' => $attendance->clock_in,
                    'requested_clock_out' => $attendance->clock_out,
                    'requested_breaks_json' => json_encode($attendance->breaks),
                    'note' => $note,
                    'status' => 'pending',
                ]);
            }

            return redirect()->route('attendance.detail', $attendance->id);
        }

        abort(403);
    }
}
