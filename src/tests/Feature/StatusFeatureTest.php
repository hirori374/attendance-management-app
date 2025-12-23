<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class StatusFeatureTest extends TestCase
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

        $this->user = User::factory()->create();
    }
    // 勤務外
    public function testStatusIsOffDuty()
    {
        $response = $this->actingAs($this->user)->get('/attendance');
        $response->assertStatus(200);

        $response->assertSee('勤務外');
    }
    // 出勤中
    public function testStatusIsOnDuty()
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => null,
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance'));

        $response->assertStatus(200);

        $response->assertSeeText('出勤中');
    }
    // 休憩中
    public function testStatusIsTakingBreak()
    {
        $attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => null,
        ]);
        Rest::factory()->create([
            'attendance_id' => $attendance->id,
            'rest_start_time' => '13:00:00',
            'rest_end_time' => null,
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance'));

        $response->assertStatus(200);

        $response->assertSeeText('休憩中');
    }
    // 退勤済み
    public function testStatusIsOffWork()
    {
        Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->format('Y-m-d'),
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($this->user)->get(route('attendance'));

        $response->assertStatus(200);

        $response->assertSeeText('退勤済');
    }
}
