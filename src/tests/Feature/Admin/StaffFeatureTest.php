<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class StaffFeatureTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // ユーザー情報、勤怠情報
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-12-01 18:00:00');

        $this->adminUser = User::factory()->create(['authority' => 'admin']);
        $this->generalUser = User::factory()->create(['authority' => 'general']);

        $this->attendances = collect([
            Attendance::factory()->create([
                'id' => 1,
                'user_id' => $this->generalUser->id,
                'date' => '2025-12-01',
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
            Attendance::factory()->create([
                'id' => 2,
                'user_id' => $this->generalUser->id,
                'date' => '2025-11-01',
                'attendance_start_time' => '11:00:00',
                'attendance_end_time' => '19:00:00',
            ]),
            Attendance::factory()->create([
                'id' => 3,
                'user_id' => $this->generalUser->id,
                'date' => '2026-01-01',
                'attendance_start_time' => '12:00:00',
                'attendance_end_time' => '20:00:00',
            ]),
        ]);
    }
    // 一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function testStaffInformationVisible()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.staff.list'));
        $response->assertStatus(200);

        $response->assertSee($this->generalUser->name);
        $response->assertSee($this->generalUser->email);
    }
    // ユーザーの勤怠情報が確認できる
    public function testStaffAttendanceVisible()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.staff', ['userId' => $this->generalUser->id]));
        $response->assertStatus(200);

        $response->assertSee(
            'data-date="2025-12-01"',
            false
        );
        $response->assertSeeInOrder([
            '2025-12-01',
            '10:00',
            '18:00',
        ]);
    }
    // 「前月」ボタンを押すと前月の情報が表示される
    public function testPreviousMonthStaffAttendance()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.staff', ['userId' => $this->generalUser->id, 'page' => 'previous']));
        $response->assertStatus(200);

        $response->assertSee(
            'data-date="2025-11-01"',
            false
        );
        $response->assertSeeInOrder([
            '2025-11-01',
            '11:00',
            '19:00',
        ]);
    }
    // 「翌月」ボタンを押すと翌月の情報が表示される
    public function testNextMonthStaffAttendance()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.staff', ['userId' => $this->generalUser->id, 'page' => 'next']));
        $response->assertStatus(200);

        $response->assertSee(
            'data-date="2026-01-01"',
            false
        );
        $response->assertSeeInOrder([
            '2026-01-01',
            '12:00',
            '20:00',
        ]);
    }
    // 「詳細」ボタンを押すとその日の勤怠詳細ページに遷移
    public function testStaffAttendanceDetail()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.attendance.detail', ['id' => 1]));
        $response->assertStatus(200);

        $response->assertSee($this->generalUser->name);
        $response->assertSee('2025年');
        $response->assertSee('12月01日');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
    }
}
