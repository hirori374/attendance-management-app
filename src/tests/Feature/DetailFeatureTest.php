<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class DetailFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // ユーザー情報、勤怠・休憩情報
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-12-01 18:00:00');

        $this->user = User::factory()->create(['authority' => 'general']);
        $this->attendance = Attendance::factory()->create([
            'id' => 1,
            'user_id' => $this->user->id,
            'date' => '2025-12-01',
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '18:00:00'
        ]);

        $this->rests = Rest::factory()->create([
            'attendance_id' => 1,
            'rest_start_time' => '12:00:00',
            'rest_end_time' => '13:00:00',
        ]);
    }
    // 詳細画面の名前確認
    public function testDisplayName(){
        $response = $this->actingAs($this->user)->get(route('attendance.detail', ['id' => 1, 'date' => $this->attendance->date->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee($this->user->name);
    }
    // 詳細画面の日付確認
    public function testDisplayDate()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.detail', ['id' => 1, 'date' => $this->attendance->date->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee('2025年');
        $response->assertSee('12月01日');
    }
    //出勤・退勤時刻がDBと一致
    public function testDisplayAttendanceStartAndEndTime()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.detail', ['id' => 1, 'date' => $this->attendance->date->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee('10:00');
        $response->assertSee('18:00');
    }
    //休憩時刻がDBと一致
    public function testDisplayRestStartAndEndTime()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.detail', ['id' => 1, 'date' => $this->attendance->date->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
