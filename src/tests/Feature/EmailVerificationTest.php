<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_sends_verification_email()
    {
        Notification::fake();

        // 会員登録
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $user = User::where('email', 'testuser@example.com')->first();

        // 認証メールが送信されたことを確認
        Notification::assertSentTo($user, VerifyEmail::class);

        // リダイレクト先を確認
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_user_can_visit_verification_link()
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        // 認証リンクを生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証リンクへアクセス
        $response = $this->actingAs($user)->get($verificationUrl);

        // メール認証が成功したことを確認
        $response->assertRedirect('/attendance'); // or 勤怠登録画面のURL
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verified_user_redirects_to_attendance_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSeeTextInOrder([
            '勤務外',
            '出勤',
        ]);
    }
}
