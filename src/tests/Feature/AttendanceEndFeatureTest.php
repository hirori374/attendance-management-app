<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceEndFeatureTest extends TestCase
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
    // 退勤処理
    public function testAttendanceEnd()
    {
        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->actingAs($this->user)->post(route('attendance.end'));
        $response->assertStatus(302);

        $response = $this->actingAs($this->user)->get(route('attendance'));
        $response->assertSeeText('退勤済');
    }
    // 退勤時刻が勤怠一覧で確認できる
    public function testAttendanceEndTimeVisibleInAttendanceList()
    {
        $this->actingAs($this->user)->post(route('attendance.end'))->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->user->id,
            'date' => '2025-12-01',
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance.list', ['yearMonth' => '2025-12']));
        $response->assertStatus(200);

        $response->assertSee(
            'data-date="2025-12-01"',
            false
        );
        $response->assertSeeInOrder([
            '2025-12-01',
            '18:00',
        ]);
    }
}
