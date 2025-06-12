<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class StampCorrectionRequestSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = Admin::first()?->id;
        $attendances = Attendance::inRandomOrder()->limit(30)->get();

        foreach ($attendances as $attendance) {
            $workDate = Carbon::parse($attendance->work_date);

            $originalClockIn = Carbon::parse($workDate->format('Y-m-d').' '.date('H:i:s', strtotime($attendance->clock_in)));
            $originalClockOut = Carbon::parse($workDate->format('Y-m-d').' '.date('H:i:s', strtotime($attendance->clock_out)));

            $requestedClockIn = (clone $originalClockIn)->subMinutes(rand(5, 20));
            $requestedClockOut = (clone $originalClockOut)->addMinutes(rand(5, 20));
            $notes = ['遅延', '業務延長'];
            StampCorrectionRequest::create([
                'attendance_id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'request_date' => $workDate->format('Y-m-d'),
                'original_clock_in' => $originalClockIn->format('Y-m-d H:i:s'),
                'original_clock_out' => $originalClockOut->format('Y-m-d H:i:s'),
                'requested_clock_in' => $requestedClockIn->format('Y-m-d H:i:s'),
                'requested_clock_out' => $requestedClockOut->format('Y-m-d H:i:s'),
                'original_breaks_json' => json_encode([
                    [
                        'break_in' => $originalClockIn->copy()->addHours(3)->format('Y-m-d H:i:s'),
                        'break_out' => $originalClockIn->copy()->addHours(3)->addMinutes(30)->format('Y-m-d H:i:s'),
                    ],
                ]),
                'requested_breaks_json' => json_encode([
                    [
                        'break_in' => $requestedClockIn->copy()->addHours(3)->format('Y-m-d H:i:s'),
                        'break_out' => $requestedClockIn->copy()->addHours(3)->addMinutes(30)->format('Y-m-d H:i:s'),
                    ],
                ]),

                'note' => Arr::random($notes),
                'status' => ['pending', 'approved'][rand(0, 1)],
                'admin_id' => $adminId,
                'reviewed_at' => now(),
            ]);
        }
    }
}
