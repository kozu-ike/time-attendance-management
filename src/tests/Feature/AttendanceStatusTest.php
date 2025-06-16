<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
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
    }

    public function test_status_is_outside_work()
    {
        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('勤務外');
    }

    public function test_status_is_working()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(2),
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('出勤中');
    }

    public function test_status_is_on_break()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);

        $attendance->breaks()->create([
            'break_in' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('休憩中');
    }

    public function test_status_is_clocked_out()
    {
        $attendance = Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now()->subHours(1),
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSeeText('退勤済');
    }
}
