<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // 今月の出勤データを2日分作成
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->format('Y-m-10'),
            'clock_in' => '2025-06-10 09:00:00',
            'clock_out' => '2025-06-10 18:00:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->format('Y-m-15'),
            'clock_in' => '2025-06-10 09:30:00',
            'clock_out' => '2025-06-10 17:30:00',
        ]);
    }

    public function test_attendance_list_displays_user_data()
    {
        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText('09:00');
        $response->assertSeeText('18:00');
        $response->assertSeeText('09:30');
        $response->assertSeeText('17:30');
    }

    public function test_current_month_is_displayed()
    {
        $month = now()->format('Y/m');  // 例: 2025/06
        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertStatus(200);

        // 月の表示（例：2025/06）をチェック
        $response->assertSeeText($month);

        // 表の日付例: 今月の最初の日を「m/d（曜日）」で作る
        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];
        $firstDay = now()->startOfMonth();
        $expectedDate = $firstDay->format('m/d') . '（' . $dayOfWeekJP[$firstDay->dayOfWeek] . '）';

        $response->assertSeeText($expectedDate);
    }


    public function test_previous_month_data_is_displayed()
    {
        $lastMonth = now()->subMonth()->format('Y/m');
        $response = $this->actingAs($this->user)->get('/attendance/list?month=' . now()->subMonth()->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText($lastMonth);

        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];
        $firstDayLastMonth = now()->subMonth()->startOfMonth();
        $expectedDate = $firstDayLastMonth->format('m/d') . '（' . $dayOfWeekJP[$firstDayLastMonth->dayOfWeek] . '）';

        $response->assertSeeText($expectedDate);
    }

    public function test_next_month_data_is_displayed()
    {
        $nextMonthCarbon = now()->addMonth();
        $nextMonth = $nextMonthCarbon->format('Y/m');

        $response = $this->actingAs($this->user)->get('/attendance/list?month=' . $nextMonthCarbon->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSeeText($nextMonth);

        $dayOfWeekJP = ['日', '月', '火', '水', '木', '金', '土'];
        $firstDayNextMonth = $nextMonthCarbon->copy()->startOfMonth();
        $expectedDate = $firstDayNextMonth->format('m/d') . '（' . $dayOfWeekJP[$firstDayNextMonth->dayOfWeek] . '）';

        $response->assertSeeText($expectedDate);
    }

    public function test_clicking_detail_button_redirects_to_detail_page()
    {
        $attendance = Attendance::where('user_id', $this->user->id)->first();

        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertStatus(200);

        $detailUrl = route('attendance.detail', $attendance->id);

        $response->assertSeeHtml('href="' . $detailUrl . '"');
    }
}
