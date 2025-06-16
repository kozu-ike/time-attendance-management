<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_detail_shows_correct_info_for_logged_in_user()
    {
        // ユーザー作成
        $user = User::factory()->create([
            'name' => '山田 太郎',
        ]);

        // 勤怠情報作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-06-15',
            'clock_in' => '2025-06-15 09:00:00',
            'clock_out' => '2025-06-15 18:00:00',
        ]);

        // 休憩情報作成
        $attendance->breaks()->create([
            'break_in' => '2025-06-15 12:00:00',
            'break_out' => '2025-06-15 12:30:00',
        ]);

        // ログイン
        $this->actingAs($user);

        // 勤怠詳細ページへアクセス
        $response = $this->get(route('attendance.detail', $attendance->id));

        // ステータスコード200
        $response->assertStatus(200);

        // 氏名表示確認
        $response->assertSeeText($user->name);

        // 日付表示確認（例：2025年06月15日）
        $response->assertSeeText(\Carbon\Carbon::parse($attendance->work_date)->format('Y年'));
        $response->assertSeeText(\Carbon\Carbon::parse($attendance->work_date)->format('n月j日'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('12:30');
    }
}
