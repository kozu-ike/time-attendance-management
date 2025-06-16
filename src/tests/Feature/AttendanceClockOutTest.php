<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\UserSeeder::class);
        $this->user = User::first();
        $this->user->email_verified_at = now();
        $this->user->save();

        // 出勤済の状態にする
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
        ]);
    }

    public function test_clock_out_button_is_displayed_and_works()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('退勤');

        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'end']);


        $after = $this->actingAs($this->user)->get('/attendance');
        $after->assertSeeText('退勤済');
    }

    public function test_clock_out_time_is_recorded_in_admin_view()
    {
        // 勤務外 → 出勤 → 退勤 の流れ
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
        ]);

        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'end']);


        // 管理画面（想定：勤怠一覧）で退勤時刻が確認できる
        $response = $this->actingAs($this->user)->get('/attendance/list?month=' . now()->format('Y-m'));
        $response->assertStatus(200);

        $attendance->refresh();
        $formattedClockOut = $attendance->clock_out?->format('H:i');

        // 実際のHTML上の退勤時刻が表示されていることを確認
        $response->assertSeeText($formattedClockOut);
    }
}
