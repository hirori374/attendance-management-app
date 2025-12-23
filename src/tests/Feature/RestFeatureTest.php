<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class RestFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    
    // ステータスが勤務中のユーザー作成
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-12-01 18:00:00');

        $this->user = User::factory()->create();
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => null,
        ]);
    }
    // 休憩開始処理
    public function testRestStart()
    {
        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        $response = $this->actingAs($this->user)->post(route('rest.start'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertSeeText('休憩中');
    }
    // 2回目の休憩開始可能
    public function testRestStartTwice()
    {
        $response = $this->actingAs($this->user)->post(route('rest.start'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->post(route('rest.end'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertSee('休憩入');
    }
    // 休憩終了処理
    public function testRestEnd()
    {
        $response = $this->actingAs($this->user)->post(route('rest.start'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertSee('休憩戻');

        $response = $this->actingAs($this->user)->post(route('rest.end'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertSeeText('出勤中');
    }
    // 2回目の休憩終了可能
    public function testRestEndTwice()
    {
        $response = $this->actingAs($this->user)->post(route('rest.start'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->post(route('rest.end'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->post(route('rest.start'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertSee('休憩戻');
    }
    // 休憩時刻が勤怠一覧画面で確認できる
    public function testRestTimeVisibleInAttendanceList()
    {
        Rest::factory()->create([
            'attendance_id' => $this->attendance->id,
            'rest_start_time' => '12:00:00',
            'rest_end_time' => '13:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.list'));
        $response->assertStatus(200);

        $response->assertSee(
            'data-date="2025-12-01',
            false
        );
        $response->assertSeeInOrder([
            '2025-12-01',
            '1:00',
        ]);
    }
}
