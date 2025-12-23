<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Rest;
use App\Services\AttendanceService;

class StaffController extends Controller
{
    // スタッフ一覧表示
    public function staffList()
    {
        $users = User::all();

        return view('admin.staff', compact('users'));
    }
    // スタッフ別勤怠一覧表示
    public function attendanceStaff(Request $request, $userId)
    {
        $user = User::find($userId);
        // 月の表示
        $yearMonth = $request->query('yearMonth') ?? Carbon::now()->format('Y-m');
        $page = $request->query('page');
        $targetMonth = AttendanceService::getTargetMonth($yearMonth, $page);
        $dates = AttendanceService::generateMonthDates($targetMonth);

        // スタッフ勤怠情報取得
        $attendances = AttendanceService::getMonthlyAttendance($userId, $targetMonth);

        foreach ($attendances as $attendance) {
            if ($attendance->attendance_start_time && $attendance->attendance_end_time) {
                $start = Carbon::createFromFormat('H:i:s', $attendance->attendance_start_time);
                $end = Carbon::createFromFormat('H:i:s', $attendance->attendance_end_time);

                $totalWorkMinutes = $end->diffInMinutes($start);

                $rests = Rest::where('attendance_id', $attendance->id)->get();
                $totalRestMinutes = 0;

                foreach ($rests as $rest) {
                    if ($rest->rest_end_time) {
                        $restStart = Carbon::createFromFormat('H:i:s', $rest->rest_start_time);
                        $restEnd = Carbon::createFromFormat('H:i:s', $rest->rest_end_time);

                        $totalRestMinutes += $restEnd->diffInMinutes($restStart);
                    }
                }

                $attendance->total_rest_time = $totalRestMinutes;
                $attendance->work_minutes = $totalWorkMinutes - $totalRestMinutes;
            } else {
                $attendance->total_rest_time = 0;
                $attendance->work_minutes = null;
            }
        }

        $displayYearMonth = $targetMonth->format('Y/m');

        return view('admin.staff_attendance_list', compact('user', 'targetMonth', 'displayYearMonth', 'dates', 'attendances'));
    }
    // CSV出力
    public function export(Request $request, $userId)
    {
        $user = User::find($userId);
        // 月の表示
        $yearMonth = $request->query('yearMonth') ?? Carbon::now()->format('Y-m');
        $targetMonth = AttendanceService::getTargetMonth($yearMonth, null);

        // スタッフ勤怠情報取得
        $attendances = AttendanceService::getMonthlyAttendance($userId, $targetMonth);

        return AttendanceService::exportMonthlyCsv($user, $targetMonth, $attendances);
    }
}
