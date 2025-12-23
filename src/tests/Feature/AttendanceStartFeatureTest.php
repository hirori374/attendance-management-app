<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStartFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;
    
    // ユーザー作成
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }
    // 出勤処理
    public function testAttendanceStart()
    {
        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->actingAs($this->user)->post(route('attendance.start'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertSeeText('出勤中');
    }
    // 出勤は1日1回のみ
    public function testAttendanceStartOnlyOncePerDay()
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }
    // 出勤時刻が勤怠一覧画面で確認できる
    public function testAttendanceStartTimeVisibleInAttendanceList()
    {
        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertSee('出勤');

        Carbon::setTestNow('2025-12-01 10:00:00');
        $this->actingAs($this->user)->post(route('attendance.start'))->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => '2025-12-01',
            'attendance_start_time' => '10:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.list', ['yearMonth' => '2025-12']));
        $response->assertStatus(200);

        $response->assertSee(
            'data-date="2025-12-01"',
            false
        );
        $response->assertSeeInOrder([
            '2025-12-01',
            '10:00',
        ]);
    }
}
