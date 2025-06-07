<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakSeeder extends Seeder
{
    public function run(): void
    {
        BreakTime::truncate();
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $breakIn = Carbon::parse($attendance->clock_in)->copy()->addHours(3);
            $breakOut = $breakIn->copy()->addMinutes(30);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_in' => $breakIn,
                'break_out' => $breakOut,
            ]);
        }
    }
}
