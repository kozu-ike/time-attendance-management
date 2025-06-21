<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
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

        Attendance::create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(3),
        ]);
    }

    public function test_break_start_button_is_shown_and_works()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeText('休憩入');

        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_start']);

        $after = $this->actingAs($this->user)->get('/attendance');
        $after->assertSeeText('休憩中');
    }

    public function test_multiple_breaks_can_be_started()
    {
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_start']);
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_end']);
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_start']);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSeeText('休憩中');
    }

    public function test_break_end_button_works()
    {
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_start']);

        $mid = $this->actingAs($this->user)->get('/attendance');
        $mid->assertSeeText('休憩中');

        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_end']);

        $after = $this->actingAs($this->user)->get('/attendance');
        $after->assertSeeText('出勤中');
    }

    public function test_multiple_break_ends_work()
    {
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_start']);
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_end']);

        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_start']);
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_end']);

        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertSeeText('出勤中');
    }

    public function test_break_times_are_shown_in_attendance_list()
    {
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_start']);
        sleep(1);
        $this->actingAs($this->user)->post('/attendance/stamp', ['action' => 'break_end']);

        $response = $this->actingAs($this->user)->get('/attendance/list?month='.now()->format('Y-m'));
        $response->assertStatus(200);

        $response->assertSeeText('休憩');
    }
}
