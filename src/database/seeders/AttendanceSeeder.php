<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            for ($i = 0; $i < 90; $i++) {
                $date = Carbon::today()->subDays($i);

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $date->copy()->setTime(9, 0)->format('Y-m-d H:i:s'),
                    'clock_out' => $date->copy()->setTime(18, 0)->format('Y-m-d H:i:s'),
                ]);

                $attendance->breaks()->create([
                    'break_in' => $date->copy()->setTime(12, 0)->format('Y-m-d H:i:s'),
                    'break_out' => $date->copy()->setTime(12, 30)->format('Y-m-d H:i:s'),
                ]);

                // breaksをロードし直して、保存して計算をトリガー
                $attendance->load('breaks');
                $attendance->save();
            }
        }
    }
}
