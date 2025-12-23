<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
    // 日別勤怠一覧表示
    public function attendanceList(Request $request)
    {
        // 日付指定
        $date = $request->query('date') ?? Carbon::now()->format('Y-m-d');
        $page = $request->query('page');
        $carbon = AttendanceService::getTargetDay($date, $page);
        $date = $carbon->format('Y-m-d');

        // 出勤ユーザー取得
        $attendances = Attendance::where('date', $date)->get();
        foreach ($attendances as $attendance) {
            $attendance->user = User::find($attendance->user_id);
        }

        // 出勤情報取得・計算
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
            } else {
                $attendance->total_rest_time = 0;
                $attendance->work_minutes = null;
            }
        }
        $titleDate = $carbon->translatedFormat('Y年m月d日');
        $indexDate = $carbon->format('Y/m/d');

        return view('admin.list', compact('date', 'attendances', 'titleDate', 'indexDate'));
    }
    // 勤怠詳細表示
    public function detail(Request $request)
    {
        $id = $request->id;
        $userId = $request->userId;
        $date = Carbon::parse($request->input('date'));
        $user = Auth::user();

        $attendance = null;
        $rests = collect();
        $attendanceCorrection = null;
        $restCorrections = [];
        $attendanceUnapproved = false;
        $restUnapproved = false;

        if ($id)
        {
            $attendance = Attendance::find($id);
            $rests = Rest::where('attendance_id', $attendance->id)->get();
            $user = $attendance->user;

            $attendanceCorrection = AttendanceCorrection::where('attendance_id', $attendance->id)->first();
            if ($attendanceCorrection && $attendanceCorrection->status === '承認待ち') {
                $attendanceUnapproved = true;
            }

            $restCorrections = RestCorrection::where('user_id', $user->id)->where('date', $date)->get()->sortByDesc('created_at')->fresh();
            $displayRows = AttendanceService::mergeRestsAndRestCorrections($rests, $restCorrections);
            $restUnapproved = $restCorrections->contains(fn ($rc) => $rc->status === '承認待ち');
        } else {
            $user = User::find($userId);
            $attendanceCorrection = AttendanceCorrection::where('user_id', $userId)->where('date', $date)->first();
            if ($attendanceCorrection && $attendanceCorrection->status === '承認待ち') {
                $attendanceUnapproved = true;
            }

            $restCorrections = RestCorrection::where('user_id', $userId)->where('date', $date)->get();
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

        return view('admin.detail', compact('user', 'date', 'attendance', 'rests', 'attendanceCorrection', 'restCorrections', 'displayRows', 'unapprovedCorrections'));
    }
}
