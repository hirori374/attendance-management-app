<?php

namespace Tests\Feature;

use App\Models\AttendanceCorrection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class CorrectionFeatureTest extends TestCase
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

        Carbon::setTestNow('2025-12-10 18:00:00');

        $this->user = User::factory()->create();
        $this->attendances = collect([
            Attendance::factory()->create([
                'id' => 1,
                'user_id' => $this->user->id,
                'date' => '2025-12-01',
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
            $this->attendance = Attendance::factory()->create([
                'id' => 2,
                'user_id' => $this->user->id,
                'date' => '2025-12-02',
                'attendance_start_time' => '10:00:00',
                'attendance_end_time' => '18:00:00',
            ]),
        ]);
    }
    // 修正申請処理後、管理者の承認外面及び申請一覧画面に表示される
    public function testCorrectionRequestVisibleInAdminCorrectionListAndCorrectionDetail()
    {
        $attendanceCorrection = AttendanceCorrection::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2025-12-01',
            'attendance_id' => 1,
            'attendance_request_start_time' => '10:00:00',
            'attendance_request_end_time' => '19:00:00',
            'remarks' => 'test',
            'status' => '承認待ち',
        ]);

        $adminUser = User::factory()->create(['authority' => 'admin']);

        $response = $this->actingAs($adminUser, 'admin')->get(route('admin.correction.request.list', ['status' => 'unapproved']));
        $response->assertStatus(200);
        $response->assertSee($attendanceCorrection->request_batch_id);
    }
    // 承認待ちの修正申請が申請一覧で確認できる
    public function testCorrectionRequestVisibleInCorrectionList()
    {
        $otherUser = User::factory()->create(['id' => $this->user->id + 1]);
        $otherAttendance = Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'date' => '2025-12-01',
            'attendance_start_time' => '10:00:00',
            'attendance_end_time' => '18:00:00',
        ]);
        $myAttendanceCorrection = AttendanceCorrection::factory()->create([
            'request_date' => '2025-12-10',
            'user_id' => $this->user->id,
            'date' => '2025-12-01',
            'attendance_id' => 1,
            'attendance_request_start_time' => '10:00:00',
            'attendance_request_end_time' => '19:00:00',
            'remarks' => 'test',
            'status' => '承認待ち',
        ]);
        $otherAttendanceCorrection = AttendanceCorrection::factory()->create([
            'request_date' => '2025-12-10',
            'user_id' => $otherUser->id,
            'date' => '2025-12-02',
            'attendance_id' => $otherAttendance->id,
            'attendance_request_start_time' => '10:00:00',
            'attendance_request_end_time' => '19:00:00',
            'remarks' => 'test',
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($this->user)->get(route('correction.request.list', ['status' => 'unapproved']));
        $response->assertStatus(200);

        $response->assertSee($myAttendanceCorrection->request_batch_id);
        $response->assertDontSee($otherAttendanceCorrection->request_batch_id);
    }
    // 承認済みページで承認された申請が確認できる
    public function testApprovedCorrectionRequestVisibleInCorrectionListApprovedPage()
    {
        $approvedAttendanceCorrection = AttendanceCorrection::factory()->create([
            'request_date' => '2025-12-10',
            'user_id' => $this->user->id,
            'date' => '2025-12-01',
            'attendance_id' => 1,
            'attendance_request_start_time' => '10:00:00',
            'attendance_request_end_time' => '19:00:00',
            'remarks' => 'test',
            'status' => '承認済み',
        ]);
        $unapprovedAttendanceCorrection = AttendanceCorrection::factory()->create([
            'request_date' => '2025-12-10',
            'user_id' => $this->user->id,
            'date' => '2025-12-02',
            'attendance_id' => 2,
            'attendance_request_start_time' => '9:00:00',
            'attendance_request_end_time' => '18:00:00',
            'remarks' => 'test',
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($this->user)->get(route('correction.request.list', ['status' => 'approved']));
        $response->assertStatus(200);

        $response->assertSee($approvedAttendanceCorrection->request_batch_id);
        $response->assertDontSee($unapprovedAttendanceCorrection->request_batch_id);
    }
    // 申請一覧→「ボタン」で詳細ページに遷移する
    public function testAttendanceCorrectionDetailWhenPushDetailButton()
    {
        $attendanceCorrections = collect([
            AttendanceCorrection::factory()->create([
                'request_date' => '2025-12-10',
                'user_id' => $this->user->id,
                'date' => '2025-12-01',
                'attendance_id' => 1,
                'attendance_request_start_time' => '10:00:00',
                'attendance_request_end_time' => '19:00:00',
                'remarks' => 'test',
                'status' => '承認待ち',
            ]),
            AttendanceCorrection::factory()->create([
                'request_date' => '2025-12-10',
                'user_id' => $this->user->id,
                'date' => '2025-12-02',
                'attendance_id' => 2,
                'attendance_request_start_time' => '9:00:00',
                'attendance_request_end_time' => '18:00:00',
                'remarks' => 'test',
                'status' => '承認待ち',
            ]),
        ]);

        $response = $this->actingAs($this->user)->get(route('correction.request.detail', ['requestBatchId' => $attendanceCorrections[0]->request_batch_id]));
        $response->assertStatus(200);

        $response->assertSee('12月01日');
        $response->assertSee('19:00');
    }
}
