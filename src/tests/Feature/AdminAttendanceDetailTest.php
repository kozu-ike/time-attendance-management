<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        // ユーザーシーダーでユーザー作成
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);

        // 管理者ユーザー取得（例：emailが'taro.y@coachtech.com'）
        $this->adminUser = User::where('email', 'taro.y@coachtech.com')->first();

        // 勤怠データ作成
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->adminUser->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now()->format('Y-m-d').' 09:00:00',
            'clock_out' => now()->format('Y-m-d').' 18:00:00',
        ]);
    }

    /** @test */
    public function admin_can_view_attendance_detail()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(route('attendance.detail', $this->attendance->id));

        $response->assertStatus(200);
        $response->assertViewHas('attendances');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function validation_fails_if_clock_in_is_after_clock_out()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(route('attendance.update'), [
                'attendances' => [
                    $this->attendance->id => [
                        'clock_in' => now()->format('Y-m-d').' 19:00:00',
                        'clock_out' => now()->format('Y-m-d').' 18:00:00',
                        'breaks' => [],
                        'remarks' => 'Valid remark',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            "attendances.{$this->attendance->id}.clock_in",
            "attendances.{$this->attendance->id}.clock_out",
        ]);
    }

    /** @test */
    public function validation_fails_if_break_in_after_clock_out()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(route('attendance.update'), [
                'attendances' => [
                    $this->attendance->id => [
                        'clock_in' => now()->format('Y-m-d').' 09:00:00',
                        'clock_out' => now()->format('Y-m-d').' 18:00:00',
                        'breaks' => [
                            ['break_in' => now()->format('Y-m-d').' 19:00:00', 'break_out' => now()->format('Y-m-d').' 19:30:00'],
                        ],
                        'remarks' => 'Valid remark',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            "attendances.{$this->attendance->id}.breaks.0.break_in",
            "attendances.{$this->attendance->id}.breaks.0.break_out",
        ]);
    }

    /** @test */
    public function validation_fails_if_break_out_after_clock_out()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(route('attendance.update'), [
                'attendances' => [
                    $this->attendance->id => [
                        'clock_in' => now()->format('Y-m-d').' 09:00:00',
                        'clock_out' => now()->format('Y-m-d').' 18:00:00',
                        'breaks' => [
                            ['break_in' => now()->format('Y-m-d').' 17:00:00', 'break_out' => now()->format('Y-m-d').' 19:00:00'],
                        ],
                        'remarks' => 'Valid remark',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            "attendances.{$this->attendance->id}.breaks.0.break_in",
            "attendances.{$this->attendance->id}.breaks.0.break_out",
        ]);
    }

    /** @test */
    public function validation_fails_if_remarks_is_empty()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->post(route('attendance.update'), [
                'attendances' => [
                    $this->attendance->id => [
                        'clock_in' => now()->format('Y-m-d').' 09:00:00',
                        'clock_out' => now()->format('Y-m-d').' 18:00:00',
                        'breaks' => [],
                        'remarks' => '',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            "attendances.{$this->attendance->id}.remarks",
        ]);
    }
}
