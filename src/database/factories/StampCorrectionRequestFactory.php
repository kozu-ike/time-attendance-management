<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),  // 勤怠データも自動生成
            'status' => 'pending',                     // デフォルトは未承認
            'request_date' => $this->faker->date(),   // ← 追加（必須）
            'requested_clock_in' => $this->faker->dateTimeBetween('-1 week', 'now')->format('Y-m-d H:i:s'),
            'requested_clock_out' => $this->faker->dateTimeBetween('now', '+1 week')->format('Y-m-d H:i:s'),
            'requested_breaks_json' => json_encode([]),  // 空配列のJSON
            'reviewed_at' => null,
            'admin_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'approved',
                'reviewed_at' => now(),
                'admin_id' => 1,
            ];
        });
    }
}
