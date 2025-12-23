<?php

namespace Tests\Feature\Admin;

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

        $this->adminUser = User::factory()->create(['authority' => 'admin']);
        $this->generalUser = User::factory()->create(['authority' => 'general']);

        $this->attendance = Attendance::factory()->create([
            'id' => 1,
            'user_id' => $this->generalUser->id,
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
    // 勤怠詳細画面に選択した勤怠のデータが表示される
    public function testAdminAttendanceDetail()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.detail', ['id' => 1, 'date' => $this->attendance->date->format('Y-m-d')]));
        $response->assertStatus(200);

        $response->assertSee($this->generalUser->name);
        $response->assertSee('2025年');
        $response->assertSee('12月01日');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
