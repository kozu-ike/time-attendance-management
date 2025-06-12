<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 1; $i < 90; $i++) {
                $date = Carbon::today()->subDays($i);

                if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                    continue;
                }

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $date->copy()->setTime(9, 0)->format('Y-m-d H:i:s'),
                    'clock_out' => $date->copy()->setTime(18, 0)->format('Y-m-d H:i:s'),
                ]);

                $attendance->load('breaks');
                $attendance->save();
            }
        }
    }
}
