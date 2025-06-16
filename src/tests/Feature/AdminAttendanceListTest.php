<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->admin = Admin::where('email', 'admin@example.com')->firstOrFail();
        $this->user = User::factory()->create(['name' => '佐藤 花子']);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
        ]);

        $this->actingAs($this->admin, 'admin');
    }

    /** @test */
    public function it_displays_today_attendance_correctly()
    {
        $response = $this->get(route('admin.attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee(now()->format('Y/m/d'));
    }

    /** @test */
    public function it_displays_previous_day_attendance()
    {
        $yesterday = now()->copy()->subDay()->toDateString();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $yesterday,
            'clock_in' => '2025-06-14 09:00:00',
            'clock_out' => '2025-06-14 18:00:00',
        ]);

        $response = $this->get(route('admin.attendance.list', ['day' => $yesterday]));
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee(Carbon::parse($yesterday)->format('Y/m/d'));
    }

    /** @test */
    public function it_displays_next_day_attendance()
    {
        $tomorrow = now()->copy()->addDay()->toDateString();

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => $tomorrow,
            'clock_in' => '2025-06-16 09:00:00',
            'clock_out' => '2025-06-16 18:00:00',
        ]);

        $response = $this->get(route('admin.attendance.list', ['day' => $tomorrow]));
        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee(Carbon::parse($tomorrow)->format('Y/m/d'));
    }
}
