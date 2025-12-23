<?php

namespace Tests\Feature\admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

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

        $this->adminUser = User::factory()->create(['authority' => 'admin']);

        $this->generalUsers = User::factory(3)->create(['authority' => 'general']);

        $this->attendances = collect([
            Attendance::factory()->create([
                'id' => 1,
                'user_id' => $this->generalUsers[0]->id,
                'date' => Carbon::today()->toDateString(),
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
            Attendance::factory()->create([
                'id' => 2,
                'user_id' => $this->generalUsers[1]->id,
                'date' => Carbon::today()->toDateString(),
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
            Attendance::factory()->create([
                'id' => 3,
                'user_id' => $this->generalUsers[2]->id,
                'date' => Carbon::today()->toDateString(),
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
            Attendance::factory()->create([
                'id' => 4,
                'user_id' => $this->generalUsers[0]->id,
                'date' => Carbon::yesterday()->toDateString(),
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
            Attendance::factory()->create([
                'id' => 5,
                'user_id' => $this->generalUsers[0]->id,
                'date' => Carbon::tomorrow()->toDateString(),
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
        ]);
    }
    // その日の全ユーザーの勤怠情報が確認できる
    public function testAdminAttendanceList()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);

        $response->assertSee('data-attendance-id="1"', false);
        $response->assertSee('data-attendance-id="2"', false);
        $response->assertSee('data-attendance-id="3"', false);

        $response->assertDontSee('data-attendance-id="4"', false);
        $response->assertDontSee('data-attendance-id="5"', false);
    }
    // 勤怠一覧遷移時に現在の日付が表示される
    public function testDisplayTodayWhenAccessAttendanceList()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.list'));

        $response->assertStatus(200);

        $response->assertSeeText('2025年12月01日の勤怠');
    }
    // 「前日」を押すと前の日の勤怠情報が表示される
    public function testPreviousDayAttendanceWhenPreviousButton()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.list', ['page' => 'previous']));
        $response->assertStatus(200);

        $response->assertSee('data-attendance-id="4"', false);

        $response->assertDontSee('data-attendance-id="1"', false);
        $response->assertDontSee('data-attendance-id="2"', false);
        $response->assertDontSee('data-attendance-id="3"', false);
        $response->assertDontSee('data-attendance-id="5"', false);
    }
    // 「翌日」を押すと次の日の勤怠情報が表示される
    public function testNextDayAttendanceWhenPreviousButton()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.list', ['page' => 'next']));
        $response->assertStatus(200);

        $response->assertSee('data-attendance-id="5"', false);

        $response->assertDontSee('data-attendance-id="1"', false);
        $response->assertDontSee('data-attendance-id="2"', false);
        $response->assertDontSee('data-attendance-id="3"', false);
        $response->assertDontSee('data-attendance-id="4"', false);
    }
}
