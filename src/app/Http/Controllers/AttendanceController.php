<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    //勤怠打刻画面表示
    public function attendance()
    {
        $user = Auth::user();
        Carbon::setLocale('ja');
        $today = Carbon::now()->translatedFormat('Y年m月d日(D)');
        $time  = now()->format('H:i');

        //勤怠ステータス
        $todayAttendance = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        //出勤前（初期値）
        $status = '勤務外';
        if ($todayAttendance){
            if(!is_null($todayAttendance->attendance_end_time)){
                $status = '退勤済';
            }
            else {
                $todayRests = Rest::where('attendance_id', $todayAttendance->id)->whereNull('rest_end_time')->exists();
                if ($todayRests){
                    $status = '休憩中';
                } else {
                    $status = '出勤中';
                }
            }
        }
        return view('attendance.stamping', compact('user', 'today', 'time', 'todayAttendance', 'status'));
    }
    //出勤打刻保存処理
    public function attendanceStart(Request $request){
        $user = Auth::user();
        $time = now()->format('H:i:s');

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'attendance_start_time' => $time,
        ]);

        return redirect()->route('attendance');
    }
    //退勤打刻保存処理
    public function attendanceEnd(Request $request){
        $user = Auth::user();
        $time  = now()->format('H:i:s');

        $todayAttendance = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $todayAttendance->attendance_end_time = $time;
        $todayAttendance->save();

        return redirect()->route('attendance');
    }
    // 休憩開始打刻保存処理
    public function restStart(Request $request){
        $user = Auth::user();
        $time  = now()->format('H:i:s');

        $todayAttendance = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();

        Rest::create([
            'attendance_id' => $todayAttendance->id,
            'rest_start_time' => $time,
        ]);

        return redirect()->route('attendance');
    }
    // 休憩終了打刻保存処理
    public function restEnd(Request $request){
        $user = Auth::user();
        $time  = now()->format('H:i:s');

        $todayAttendance = Attendance::where('user_id', $user->id)->whereDate('date', Carbon::today())->first();
        $todayRest = Rest::where('attendance_id', $todayAttendance->id)->whereNull('rest_end_time')->first();
        $todayRest->rest_end_time = $time;
        $todayRest->save();

        return redirect()->route('attendance');
    }
}
