<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    /**
     * 修正申請を承認し、勤務時間と休憩時間を更新する
     */
    public function approve(Request $request, int $id)
    {
        $correction = StampCorrectionRequest::findOrFail($id);

        $attendance = Attendance::findOrFail($correction->attendance_id);
        $attendance->clock_in = $correction->requested_clock_in;
        $attendance->clock_out = $correction->requested_clock_out;
        $attendance->save();

        BreakTime::where('attendance_id', $attendance->id)->delete();

        $breaks = json_decode($correction->requested_breaks_json, true);
        foreach ($breaks as $break) {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_in' => $break['break_in'],
                'break_out' => $break['break_out'],
            ]);
        }

        $correction->status = 'approved';
        $correction->reviewed_at = now();
        $correction->admins_id = Auth::id();
        $correction->save();

        return redirect()->back()->with('success', '修正申請を承認しました。');
    }

    /**
     * 修正申請一覧
     */
    public function index()
    {
        $isAdmin = Auth::user()->is_admin;

        $corrections = StampCorrectionRequest::with(['attendance.user'])
            ->when(!$isAdmin, function ($query) {
                $query->whereHas('attendance', function ($subQuery) {
                    $subQuery->where('user_id', Auth::id());
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shared.stamp_correction_request_list', compact('corrections', 'isAdmin'));
    }
}
