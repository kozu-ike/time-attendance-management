<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminUserAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $generalUser;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'UserSeeder']);

        $this->adminUser = User::where('email', 'taro.y@coachtech.com')->first();

        $this->generalUser = User::where('email', '!=', 'taro.y@coachtech.com')->first();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->generalUser->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => now()->format('Y-m-d') . ' 09:00:00',
            'clock_out' => now()->format('Y-m-d') . ' 18:00:00',
        ]);
    }


    /** @test */
    public function admin_can_view_all_general_users_list()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(route('admin.attendance.staff_list')); // ルート名は適宜修正してください

        $response->assertStatus(200);

        // 一般ユーザーの氏名とメールアドレスが表示されていることを確認
        $response->assertSee($this->generalUser->name);
        $response->assertSee($this->generalUser->email);
        
    }

    /** @test */
    public function admin_can_view_selected_users_attendance_list_for_current_month()
    {
        $month = now()->format('Y-m');

        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(route('admin.attendance.staff', ['user_id' => $this->generalUser->id, 'month' => $month]));

        $response->assertStatus(200);

        // 勤怠の打刻時刻（09:00, 18:00）が表示されているか
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // ユーザー名も表示されているか
        $response->assertSee($this->generalUser->name);
    }

    /** @test */
    public function admin_can_view_previous_month_attendance()
    {
        $currentMonth = Carbon::now();
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');

        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(route('admin.attendance.staff', ['user_id' => $this->generalUser->id, 'month' => $prevMonth]));

        $response->assertStatus(200);

        // 表示月が前月になっていることの簡単なチェック
        $response->assertViewHas('month', $prevMonth);
    }

    /** @test */
    public function admin_can_view_next_month_attendance()
    {
        $currentMonth = Carbon::now();
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(route('admin.attendance.staff', ['user_id' => $this->generalUser->id, 'month' => $nextMonth]));

        $response->assertStatus(200);

        // 表示月が翌月になっていることの簡単なチェック
        $response->assertViewHas('month', $nextMonth);
    }

    /** @test */
    public function admin_can_access_attendance_detail_page()
    {
        $response = $this->actingAs($this->adminUser, 'admin')
            ->get(route('attendance.detail', $this->attendance->id));

        $response->assertStatus(200);

        // 勤怠の打刻時刻が表示されているか
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
