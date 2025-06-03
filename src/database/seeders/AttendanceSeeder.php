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

                Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in' => $date->copy()->setTime(9, 0),
                    'clock_out' => $date->copy()->setTime(18, 0),
                ]);
            }
        }
    }
}
