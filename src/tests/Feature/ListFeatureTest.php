<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class ListFeatureTest extends TestCase
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

        Carbon::setTestNow('2025-12-01 18:00:00');

        $this->user = User::factory()->create(['authority' => 'general']);
        $this->attendance = collect([
            Attendance::factory()->create([
                'id' => 1,
                'user_id' => $this->user->id,
                'date' => '2025-12-01',
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00'
            ]),
            Attendance::factory()->create([
                'id' => 2,
                'user_id' => $this->user->id,
                'date' => '2025-12-02',
                'attendance_start_time' => '11:00:00',
                'attendance_end_time' => '19:00:00'
            ]),
            Attendance::factory()->create([
                'id' => 3,
                'user_id' => $this->user->id,
                'date' => '2025-11-01',
                'attendance_start_time' => '12:00:00',
                'attendance_end_time' => '20:00:00'
            ]),
            Attendance::factory()->create([
                'id' => 4,
                'user_id' => $this->user->id,
                'date' => '2026-01-01',
                'attendance_start_time' => '11:00:00',
                'attendance_end_time' => '20:00:00'
            ]),
        ]);

        $this->rests = collect([
            Rest::factory()->create([
                'attendance_id' => 1,
                'rest_start_time' => '12:00:00',
                'rest_end_time' => '13:00:00',
            ]),
            Rest::factory()->create([
                'attendance_id' => 3,
                'rest_start_time' => '14:00:00',
                'rest_end_time' => '15:00:00',
            ]),
            Rest::factory()->create([
                'attendance_id' => 4,
                'rest_start_time' => '13:00:00',
                'rest_end_time' => '14:00:00',
            ]),
        ]);
    }
    // 勤怠一覧に勤怠情報が表示されている
    public function testAllAttendancesVisibleInAttendanceList()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.list', ['yearMonth' => '2025-12']));

        $response->assertSee(
            'data-date="2025-12-01"',
            false
        );
        $response->assertSee(
            'data-date="2025-12-02"',
            false
        );
        $response->assertSeeInOrder([
            '2025-12-01',
            '10:00',
            '18:00',
            '1:00',
            '7:00',
        ]);
        $response->assertSeeInOrder([
            '2025-12-02',
            '11:00',
            '19:00',
            '8:00',
        ]);
    }
    // 勤怠一覧アクセス時に現在の月が表示
    public function testAttendancesOfThisMonthWhenAccessAttendanceList()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.list'));

        $response->assertSee('2025/12');
    }
    // 「前月」を押すと表示月の前月が表示
    public function testAttendanceOfLastMonth()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.list'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('attendance.list', ['yearMonth' => Carbon::today()->format('Y-m'), 'page' => 'previous']));
        $response->assertStatus(200);

        $response->assertSee('2025/11');
        $response->assertSee(
            'data-date="2025-11-01"',
            false
        );
        $response->assertSeeInOrder([
            '2025-11-01',
            '12:00',
            '20:00',
            '1:00',
            '7:00',
        ]);
    }
    // 「翌月」を押すと表示月の翌月が表示
    public function testAttendanceOfNextMonth()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.list'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('attendance.list', ['yearMonth' => Carbon::today()->format('Y-m'), 'page' => 'next']));
        $response->assertStatus(200);

        $response->assertSee('2026/01');
        $response->assertSee(
            'data-date="2026-01-01"',
            false
        );
        $response->assertSeeInOrder([
            '2026-01-01',
            '11:00',
            '20:00',
            '1:00',
            '8:00',
        ]);
    }
    // 「詳細」を押すとその日の勤怠詳細画面に遷移
    public function testAttendanceDetailWhenPushDetailButton()
    {
        $response = $this->actingAs($this->user)->get(route('attendance.list'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->get(route('attendance.detail', ['id' => 1, 'date' => Carbon::today()->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee('勤怠詳細');
        $response->assertSee('2025年');
        $response->assertSee('12月01日');
    }
}
