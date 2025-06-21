<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
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

    public function test_clock_in_button_is_shown_and_works()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('出勤');

        $postResponse = $this->actingAs($this->user)->post('/attendance/stamp', [
            'action' => 'start',
        ]);
        $postResponse->assertRedirect('/attendance');

        $after = $this->actingAs($this->user)->get('/attendance');
        $after->assertStatus(200);
        $after->assertSeeText('出勤中');
    }

    public function test_clock_in_button_not_shown_if_already_clocked_out()
    {
        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now()->subHours(1),
        ]);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSeeText('出勤');
    }

    public function test_clock_in_time_is_recorded_in_admin_view()
    {
        $this->actingAs($this->user)->post('/attendance/stamp', [
            'action' => 'start',
        ]);

        $attendance = Attendance::where('user_id', $this->user->id)
            ->where('work_date', now()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertNotNull($attendance->clock_in);
        $this->assertEquals($attendance->work_date, now()->toDateString());

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
        ]);
    }
}
