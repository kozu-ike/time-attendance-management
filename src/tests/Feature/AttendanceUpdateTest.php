<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Attendance $attendance;

    protected \App\Models\Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['name' => '山田 太郎']);
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => '2025-06-15',
            'clock_in' => '2025-06-15 09:00:00',
            'clock_out' => '2025-06-15 18:00:00',
        ]);

        $this->actingAs($this->user);

        // Seeder実行と管理者取得
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->admin = \App\Models\Admin::where('email', 'admin@example.com')->firstOrFail();
    }

    public function test_clock_in_after_clock_out_shows_error()
    {
        $response = $this->post(route('attendance.update'), [
            'attendances' => [
                $this->attendance->id => [
                    'clock_in' => '19:00',
                    'clock_out' => '18:00',
                    'remarks' => '備考',
                    'breaks' => [],
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['attendances.'.$this->attendance->id.'.clock_in']);
        $response->assertSessionHasErrors(['attendances.'.$this->attendance->id.'.clock_out']);
        $this->assertStringContainsString('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first());
    }

    public function test_break_in_after_clock_out_shows_error()
    {
        $response = $this->post(route('attendance.update'), [
            'attendances' => [
                $this->attendance->id => [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'remarks' => '備考',
                    'breaks' => [
                        ['break_in' => '19:00', 'break_out' => '19:30'],
                    ],
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['attendances.'.$this->attendance->id.'.breaks.0.break_in']);
        $this->assertStringContainsString('休憩時間が勤務時間外です', session('errors')->first());
    }

    public function test_break_out_after_clock_out_shows_error()
    {
        $response = $this->post(route('attendance.update'), [
            'attendances' => [
                $this->attendance->id => [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'remarks' => '備考',
                    'breaks' => [
                        ['break_in' => '12:00', 'break_out' => '19:00'],
                    ],
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['attendances.'.$this->attendance->id.'.breaks.0.break_out']);
        $this->assertStringContainsString('休憩時間が勤務時間外です', session('errors')->first());
    }

    public function test_empty_remarks_shows_error()
    {
        $response = $this->post(route('attendance.update'), [
            'attendances' => [
                $this->attendance->id => [
                    'clock_in' => '09:00',
                    'clock_out' => '18:00',
                    'remarks' => '',
                    'breaks' => [],
                ],
            ],
        ]);

        $response->assertSessionHasErrors(['attendances.'.$this->attendance->id.'.remarks']);
        $this->assertStringContainsString('備考を記入してください', session('errors')->first());
    }
}
