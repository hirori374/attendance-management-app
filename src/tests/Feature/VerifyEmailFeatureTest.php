<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerifyEmailFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }
    // 会員登録後、認証メール送信
    public function testVerificationEmailIsSentOnRegister()
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Taro',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }
    //メール認証完了後、勤怠登録画面へ遷移
    public function testEmailAuthenticationComplete()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/email/verify/' . $user->id . '/' . sha1($user->email));
        $response->assertRedirect('/attendance');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
