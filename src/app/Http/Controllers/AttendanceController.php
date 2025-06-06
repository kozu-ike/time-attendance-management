<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;


class AttendanceController extends Controller
{

    
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
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

        return view('attendance.index', compact('status'));
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
                    $lastBreak = $attendance->breaks()->latest('id')->first();
                    if ($lastBreak && !$lastBreak->break_out) {
                        // 休憩終了していない場合は退勤不可
                        break;
                    }
                    $attendance->clock_out = now();
                    $attendance->save();
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
        $month = $request->input('month', date('Y-m'));
        $userId = Auth::id();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $userId)
            ->whereYear('work_date', '=', substr($month, 0, 4))
            ->whereMonth('work_date', '=', substr($month, 5, 2))
            ->orderBy('work_date', 'desc')
            ->get();
        $monthFormatted = substr($month, 0, 7); // "2025-04"

        $currentMonth = Carbon::parse($month)->format('Y年m月');  // そのままCarbonを使う

        return view('attendance.list', compact('attendances', 'month', 'currentMonth'));
    }

    public function detail($attendance_id)
    {
        $user = Auth::user();

        $attendance = Attendance::with('breaks')
            ->where('id', $attendance_id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        $attendances = collect([$attendance]);
        return view('attendance.detail', compact('attendances'));
    }


    public function update(AttendanceRequest $request)
    {
        $user = Auth::user();
        $attendancesInput = $request->input('attendances', []);

        foreach ($attendancesInput as $attendanceId => $data) {
            $attendance = Attendance::where('id', $attendanceId)
                ->where('user_id', $user->id)
                ->first();

            if (!$attendance) {
                continue;
            }

            $attendance->work_date = $data['work_date'] ?? $attendance->work_date;
            $attendance->clock_in = $data['clock_in'] ?? null;
            $attendance->clock_out = $data['clock_out'] ?? null;
            $attendance->remarks = $data['remarks'] ?? '';

            $attendance->breaks()->delete();

            if (isset($data['breaks']) && is_array($data['breaks'])) {
                foreach ($data['breaks'] as $break) {
                    if (!empty($break['break_in']) && !empty($break['break_out'])) {
                        $attendance->breaks()->create([
                            'break_in' => $break['break_in'],
                            'break_out' => $break['break_out'],
                        ]);
                    }
                }
            }

            // 修正申請レコードを作成し、変数に代入
            $correction = StampCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $user->id,
                'request_date' => now()->toDateString(),
                'status' => 'pending',
                'note' => 'ユーザーによる修正申請',
            ]);

            // 1件のみ申請してリダイレクトする想定ならここで return
            return redirect()->route('stamp_correction_request.approve', $correction->id)
                ->with('success', '修正申請を送信しました。');
        }

        // すべての勤怠がスキップされた場合のフォールバック
        return redirect()->back()->with('error', '修正できる勤怠データが見つかりませんでした。');
    }
}
