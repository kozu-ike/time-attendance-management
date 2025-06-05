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
     * 申請一覧（管理者は全件、ユーザーは自分の申請のみ）
     */
    public function index()
    {
        $user = Auth::user();
        $isAdmin = Auth::guard('admin')->check();

        $corrections = StampCorrectionRequest::with(['attendance.user'])
            ->when(!$isAdmin, function ($query) use ($user) {
                $query->whereHas('attendance', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shared.stamp_correction_request_list', compact('corrections', 'isAdmin'));
    }

    /**
     * 修正申請の承認画面表示
     * ルートでeditApproveという名前なのでここで定義
     */
    public function editApprove($id)
    {
        $correction = StampCorrectionRequest::with(['attendance.user'])->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('correction'));
    }

    /**
     * 修正申請を承認し、勤務時間と休憩時間を更新する
     */
    public function approve(Request $request, $id)
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
        $correction->admins_id = Auth::id();  // 管理者IDとしてログインIDをセット
        $correction->save();

        return redirect()->route('admin.stamp_correction_request.list')->with('success', '修正申請を承認しました。');
    }

    
}
