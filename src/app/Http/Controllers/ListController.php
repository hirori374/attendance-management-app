<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;
use Illuminate\Support\Facades\Auth;
use App\Services\AttendanceService;

class ListController extends Controller
{
    // 勤怠一覧表示
    public function list(Request $request)
    {
        // 月の表示
        $yearMonth = $request->query('yearMonth') ?? Carbon::now()->format('Y-m');
        $page = $request->query('page');
        $targetMonth = AttendanceService::getTargetMonth($yearMonth, $page);
        $dates = AttendanceService::generateMonthDates($targetMonth);

        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        // 勤怠情報取得
        $user = Auth::user();
        $attendances = AttendanceService::getMonthlyAttendance($user->id, $targetMonth);

        foreach ($attendances as $attendance) {
            if ($attendance->attendance_start_time && $attendance->attendance_end_time) {
                $attendanceStartTime = Carbon::createFromFormat('H:i:s', $attendance->attendance_start_time);
                $attendanceEndTime = Carbon::createFromFormat('H:i:s', $attendance->attendance_end_time);
                $totalWorkMinutes = $attendanceEndTime->diffInMinutes($attendanceStartTime);

                $rests = Rest::where('attendance_id', $attendance->id)->get();
                $totalRestMinutes = 0;

                foreach ($rests as $rest) {
                    if ($rest->rest_end_time) {
                        $restStartTime = Carbon::createFromFormat('H:i:s', $rest->rest_start_time);
                        $restEndTime = Carbon::createFromFormat('H:i:s', $rest->rest_end_time);
                        $totalRestMinutes += $restEndTime->diffInMinutes($restStartTime);
                    }
                }

                $attendance->total_rest_time = $totalRestMinutes;
                $attendance->work_minutes = $totalWorkMinutes - $totalRestMinutes;
            } elseif($attendance->attendance_start_time && $attendance->attendance_end_time ===null) {
                $rests = Rest::where('attendance_id', $attendance->id)->get();
                $totalRestMinutes = 0;

                foreach ($rests as $rest) {
                    if ($rest->rest_end_time) {
                        $restStartTime = Carbon::createFromFormat('H:i:s', $rest->rest_start_time);
                        $restEndTime = Carbon::createFromFormat('H:i:s', $rest->rest_end_time);
                        $totalRestMinutes += $restEndTime->diffInMinutes($restStartTime);
                    }
                }
                $attendance->total_rest_time = $totalRestMinutes;
                $attendance->work_minutes = null;
            }
            else {
                $attendance->total_rest_time = 0;
                $attendance->work_minutes = null;
            }
        }
        $displayYearMonth = $targetMonth->format('Y/m');

        return view('attendance.list', compact('user', 'targetMonth', 'displayYearMonth', 'dates', 'attendances'));
    }
    // 勤怠詳細表示
    public function detail(Request $request)
    {
        $id = $request->id;
        $date = Carbon::parse($request->input('date'));
        $user = Auth::user();

        $attendance = null;
        $rests = collect();
        $attendanceCorrection = null;
        $restCorrections = [];
        $attendanceUnapproved = false;
        $restUnapproved = false;

        $displayRows = 0;

        if ($id)
        {
            $attendance = Attendance::find($id);
            $rests = Rest::where('attendance_id', $attendance->id)->get()->fresh();

            $attendanceCorrection = AttendanceCorrection::where('attendance_id', $attendance->id)->first();
            if ($attendanceCorrection && $attendanceCorrection->status === '承認待ち') {
                $attendanceUnapproved = true;
            }

            $restCorrections = RestCorrection::where('user_id', $user->id)->where('date', $date)->get()->sortByDesc('created_at')->fresh();
            $displayRows = AttendanceService::mergeRestsAndRestCorrections($rests, $restCorrections);
            $restUnapproved = $restCorrections->contains(fn ($rc) => $rc->status === '承認待ち');
        } else {
            $attendanceCorrection = AttendanceCorrection::where('user_id', $user->id)->where('date', $date)->first();
            if ($attendanceCorrection && $attendanceCorrection->status === '承認待ち') {
                $attendanceUnapproved = true;
            }

            $restCorrections = RestCorrection::where('user_id', $user->id)->where('date', $date)->get();
            $displayRows = AttendanceService::mergeRestsAndRestCorrections($rests, $restCorrections);
            foreach ($restCorrections as $restCorrection){
                $key = $restCorrection->rest_id ?? 0;
                $restCorrections[$key] = $restCorrection;
                if ($restCorrection && $restCorrection->status === '承認待ち') {
                    $restUnapproved = true;
                }
            }
        }

        $unapprovedCorrections = $attendanceUnapproved || $restUnapproved;

        return view('attendance.detail', compact('user', 'date', 'attendance', 'rests', 'attendanceCorrection', 'restCorrections', 'displayRows', 'unapprovedCorrections'));
    }
}
