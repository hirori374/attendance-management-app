<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;

class AdminCorrectionFeatureTest extends TestCase
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
                'date' => '2025-12-02',
                'attendance_start_time' => '11:00:00',
                'attendance_end_time' => '19:00:00',
            ]),
        ]);

        $this->unapprovedAttendanceCorrection = AttendanceCorrection::factory()->create([
            'user_id' => $this->generalUser->id,
            'date' => '2025-12-01',
            'attendance_id' => 1,
            'attendance_request_start_time' => '10:00:00',
            'attendance_request_end_time' => '19:00:00',
            'remarks' => 'test',
            'status' => '承認待ち',
        ]);
        $this->approvedAttendanceCorrection = AttendanceCorrection::factory()->create([
            'user_id' => $this->generalUser->id,
            'date' => '2025-12-02',
            'attendance_id' => 2,
            'attendance_request_start_time' => '12:00:00',
            'attendance_request_end_time' => '19:00:00',
            'remarks' => 'test',
            'status' => '承認済み',
        ]);
    }
    // 承認待ちの修正申請が表示される
    public function testUnapprovedCorrectionVisible()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.correction.request.list', ['status' => 'unapproved']));
        $response->assertStatus(200);

        $response->assertSee($this->unapprovedAttendanceCorrection->request_batch_id, false);
        $response->assertDontSee($this->approvedAttendanceCorrection->request_batch_id, false);
    }
    // 承認済みの修正申請が表示される
    public function testApprovedCorrectionVisible()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.correction.request.list', ['status' => 'approved']));
        $response->assertStatus(200);

        $response->assertSee($this->approvedAttendanceCorrection->request_batch_id, false);
        $response->assertDontSee($this->unapprovedAttendanceCorrection->request_batch_id, false);
    }
    // 修正申請の詳細が表示される
    public function testCorrectionDetail()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->get(route('admin.request.detail', ['requestBatchId' => $this->unapprovedAttendanceCorrection->request_batch_id]));
        $response->assertStatus(200);

        $response->assertSee($this->generalUser->name);
        $response->assertSee('2025年');
        $response->assertSee('12月01日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
    // 修正申請の承認処理
    public function testCorrectionApproved()
    {
        $response = $this->actingAs($this->adminUser, 'admin')->post(route('admin.approve', ['requestBatchId' => $this->unapprovedAttendanceCorrection->request_batch_id]));
        $response->assertStatus(302);

        $response = $this->actingAs($this->adminUser)->get(route('admin.approve', ['requestBatchId' => $this->unapprovedAttendanceCorrection->request_batch_id]));
        $response->assertStatus(200);
        $response->assertSeeText('承認済み');

        $this->assertDatabaseHas('attendance_corrections', [
            'request_batch_id' => $this->unapprovedAttendanceCorrection->request_batch_id,
            'status' => '承認済み',
        ]);
        $this->assertDatabaseHas('attendances', [
            'id' => 1,
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '19:00:00',
        ]);
    }
}
