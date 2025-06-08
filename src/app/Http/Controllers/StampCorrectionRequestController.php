<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;


class StampCorrectionRequestController extends Controller
{
    public function editApprove($id)
    {
        $user = Auth::user();
        $admin = Auth::guard('admin')->user();

        if (!$user && !$admin) {
            abort(403, 'ログインしていません');
        }

        $correction = StampCorrectionRequest::with(['attendance.user'])->findOrFail($id);
        $attendance = Attendance::with('breaks', 'user')->findOrFail($correction->attendance_id);

        $attendance = $correction->attendance;

        return view('stamp_correction_request.approve', [
            'correction' => $correction,
            'attendance' => $attendance,

            'isAdmin' => $admin !== null,
        ]);
    }

    public function approve(StampCorrectionRequest $correction)
    {
        $attendance = Attendance::findOrFail($correction->attendance_id);

        $attendance->clock_in = $correction->requested_clock_in;
        $attendance->clock_out = $correction->requested_clock_out;
        $attendance->save();

        BreakTime::where('attendance_id', $attendance->id)->delete();

        $breaks = json_decode($correction->requested_breaks_json, true);
        if (is_array($breaks)) {
            foreach ($breaks as $break) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_in' => $break['break_in'],
                    'break_out' => $break['break_out'],
                ]);
            }

            $correction->status = 'approved';
            $correction->reviewed_at = now();
            $correction->admin_id = Auth::guard('admin')->id();
            $correction->save();
            return redirect()->back();
        }
    }


    public function index(Request $request)
    {
        if (!Auth::check() && !Auth::guard('admin')->check()) {
            return redirect()->route('login');
        }

        $isAdmin = Auth::guard('admin')->check();
        $user = $isAdmin ? Auth::guard('admin')->user() : Auth::user();

        $status = $request->query('status', 'pending');  // 承認状態を取得

        $corrections = \App\Models\StampCorrectionRequest::with(['attendance.user'])
            ->when(!$isAdmin, function ($query) use ($user) {
                $query->whereHas('attendance', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                });
            })
            ->when(in_array($status, ['pending', 'approved']), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stamp_correction_request.list', compact('corrections', 'status', 'isAdmin'));
    }
}
