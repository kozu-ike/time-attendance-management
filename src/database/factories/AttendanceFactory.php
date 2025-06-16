<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = $this->faker->dateTimeBetween('-1 months', 'now');
        $clockIn = Carbon::parse($date)->setTime(rand(8, 10), 0);
        $clockOut = (clone $clockIn)->addHours(8);

        return [
            'user_id' => User::factory(),
            'work_date' => $clockIn->format('Y-m-d'),
            'clock_in' => $clockIn->format('H:i:s'),
            'clock_out' => $clockOut->format('H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
