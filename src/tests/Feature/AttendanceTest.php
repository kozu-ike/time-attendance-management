<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seederを実行
        $this->seed(\Database\Seeders\UserSeeder::class);
    }

    public function test_attendance_page_shows_current_date_time()
    {
        $user = User::first();
        $this->assertNotNull($user);
        $user->email_verified_at = now();
        $user->save();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $today = now();
        $weekday = ['日', '月', '火', '水', '木', '金', '土'][$today->dayOfWeek];
        $expectedDate = $today->format('Y年m月d日')."（{$weekday}）";

        $response->assertSeeText($expectedDate);
    }
}
