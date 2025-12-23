<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;
use App\Http\Requests\CorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\AttendanceService;

class CorrectionController extends Controller
{
    //勤怠・休憩修正申請
    public function correctionRequest(CorrectionRequest $request, $id)
    {
        $user = Auth::user();
        $attendance = Attendance::findOrFail($id);
        $attendanceDate = $attendance->date->format('Y-m-d');
        $rests = Rest::where('attendance_id', $id)->get();
        $today = Carbon::now();
        $batchId = (string) Str::uuid();

        // リクエストをH:i:sの形式にする(string)
        $attendanceStartTimeRequest = $request->attendance_start_time ? $request->attendance_start_time . ':00' : null;
        $attendanceEndTimeRequest = $request->attendance_end_time ? $request->attendance_end_time . ':00' : null;

        // リクエストとDBを比較(string同士)
        if ($attendance->attendance_start_time !== $attendanceStartTimeRequest || $attendance->attendance_end_time !== $attendanceEndTimeRequest)
        {
            AttendanceCorrection::create([
                'request_batch_id' => $batchId,
                'request_date' => $today,
                'user_id' => $user->id,
                'date' => $attendance->date,
                'attendance_id' => $attendance->id,
                'attendance_request_start_time' => $attendanceStartTimeRequest,
                'attendance_request_end_time' => $attendanceEndTimeRequest,
                'remarks' => $request->input('remarks'),
            ]);
        }

        foreach ($rests as $index => $rest) {
            $rest = $rests[$index] ?? null;
            $restStartTimeRequest = $request->input('rest_start_time')[$index] ?? null;
            $restEndTimeRequest = $request->input('rest_end_time')[$index] ?? null;

            if ($restStartTimeRequest) {
                $restStartTimeRequest = $restStartTimeRequest ? $restStartTimeRequest . ':00' : null;
            }
            if ($restEndTimeRequest) {
                $restEndTimeRequest = $restEndTimeRequest ? $restEndTimeRequest . ':00' : null;
            }
            
            if ($rest->rest_start_time !== $restStartTimeRequest || $rest->rest_end_time !== $restEndTimeRequest) {
                RestCorrection::create([
                    'request_batch_id' => $batchId,
                    'request_date' => $today,
                    'user_id' => $user->id,
                    'date' => $rest->attendance->date,
                    'rest_id' => $rest->id,
                    'rest_request_start_time' => $restStartTimeRequest,
                    'rest_request_end_time' => $restEndTimeRequest,
                    'remarks' => $request->input('remarks'),
                ]);
            }
        }

        $newRestCorrectionIndex = $rests->count() ?? 0;
        if($request->input('rest_start_time')[$newRestCorrectionIndex] !== null || $request->input('rest_end_time')[$newRestCorrectionIndex] !== null){
            $newRestStartTimeRequest = $request->input('rest_start_time')[$newRestCorrectionIndex] ?? null;
            $newRestEndTimeRequest = $request->input('rest_end_time')[$newRestCorrectionIndex] ?? null;
            if ($newRestStartTimeRequest) {
                $newRestStartTimeRequest = $newRestStartTimeRequest ? $newRestStartTimeRequest . ':00' : null;
            }
            if ($newRestEndTimeRequest) {
                $newRestEndTimeRequest = $newRestEndTimeRequest ? $newRestEndTimeRequest . ':00' : null;
            }

            RestCorrection::create([
                'request_batch_id' => $batchId,
                'request_date' => $today,
                'user_id' => $user->id,
                'date' => $attendance->date,
                'rest_id' => null,
                'rest_request_start_time' => $newRestStartTimeRequest,
                'rest_request_end_time' => $newRestEndTimeRequest,
                'remarks' => $request->input('remarks'),
            ]);
        }

        return redirect()->route('attendance.detail', ['id' => $id, 'date' => $attendanceDate]);
    }
    // 勤怠・休憩新規作成申請
    public function createRequest(CorrectionRequest $request)
    {
        $user = Auth::user();
        $today = Carbon::now();
        $date = $request->input('date');
        $batchId = (string) Str::uuid();

        // attendance
        $attendanceStartTimeRequest = $request->attendance_start_time ? $request->attendance_start_time . ':00' : null;
        $attendanceEndTimeRequest = $request->attendance_end_time ? $request->attendance_end_time . ':00' : null;

        AttendanceCorrection::create([
            'request_batch_id' => $batchId,
            'request_date' => $today,
            'user_id' => $user->id,
            'date' => $date,
            'attendance_id' => null,
            'attendance_request_start_time' => $attendanceStartTimeRequest,
            'attendance_request_end_time' => $attendanceEndTimeRequest,
            'remarks' => $request->input('remarks'),
        ]);

        // rests
        $restStartTimeRequest = $request->input('rest_start_time') ?? null;
        $restEndTimeRequest = $request->input('rest_end_time') ?? null;

        if (is_string($restStartTimeRequest)) {
            $restStartTimeRequest = trim($restStartTimeRequest);

            if ($restStartTimeRequest === '') {
                $restStartTimeRequest = null;
            }
        }
        try {
            if ($restStartTimeRequest !== null) {
                $restStartTimeRequest = $restStartTimeRequest ? $restStartTimeRequest . ':00' : null;
            }
        } catch (\Exception $e) {
            $restStartTimeRequest = null;
        }
        if (is_string($restEndTimeRequest)) {
            $restEndTimeRequest = trim($restEndTimeRequest);

            if ($restEndTimeRequest === '') {
                $restEndTimeRequest = null;
            }
        }
        try {
            if ($restEndTimeRequest !== null) {
                $restEndTimeRequest = $restEndTimeRequest ? $restEndTimeRequest . ':00' : null;
            }
        } catch (\Exception $e) {
            $restEndTimeRequest = null;
        }

        if ($restStartTimeRequest || $restEndTimeRequest !== null) {
            RestCorrection::create([
                'request_batch_id' => $batchId,
                'request_date' => $today,
                'user_id' => $user->id,
                'date' => $date,
                'rest_id' => null,
                'rest_request_start_time' => $restStartTimeRequest,
                'rest_request_end_time' => $restEndTimeRequest,
                'remarks' => $request->input('remarks'),
            ]);
        }

        return redirect()->route('attendance.detail', ['date' => $date]);
    }
    //勤怠・休憩修正申請一覧表示
    public function correctionRequestList(Request $request)
    {
        // Correctionのタイプを定義
        $attendanceCorrections = AttendanceCorrection::with('user')->get()->map(function ($item) {
            $item->type = 'attendance';
            return $item;
        });
        $restCorrections = RestCorrection::with('user')->get()->map(function ($item) {
            $item->type = 'rest';
            return $item;
        });
        // 勤怠と休憩の修正申請を結合
        $allCorrections = $attendanceCorrections->merge($restCorrections)->sortByDesc('created_at');
        // 同じattendance_idとそれに紐づくrest_idは1件の申請にまとめる
        $groupedCorrections = $allCorrections->groupBy('request_batch_id')
            ->map(function ($group) {
                $first = $group->first();
                $user = $first->user;
                $date = $first->date;

                // 勤怠・休憩情報がある場合
                $attendanceId = null;
                if ($first->attendance_id != null || $first->rest_id !== null){
                    $attendanceId = $first->type === 'attendance' ? $first->attendance->id : $first->rest->attendance->id;
                }

                return [
                    'request_batch_id' => $first->request_batch_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'date' => $date,
                    'types' => $group->pluck('type')->unique()->join(', '),
                    'status' => $group->pluck('status')->unique()->join(', '),
                    'remarks' => $group->pluck('remarks')->unique()->join(', '),
                    'request_date' => $group->max('request_date'),
                    'attendance_id' => $attendanceId,
                ];
            })
            ->values();

        // 自分が申請したもののみ表示
        if (Auth::User()->authority === 'general'){
            $groupedCorrections = $groupedCorrections->where('user_id', Auth::User()->id);
        }

        // 承認ステータス
        $status = $request->query('status');
        if ($status === 'unapproved') {
            $groupedCorrections = $groupedCorrections->where('status', '承認待ち');
        } elseif ($status === 'approved') {
            $groupedCorrections = $groupedCorrections->where('status', '承認済み');
        }

        return view('correction.list', compact('groupedCorrections'));
    }
    // 申請詳細表示
    public function correctionRequestDetail($requestBatchId)
    {
        // 修正申請情報取得、勤怠・休憩情報取得
        $attendanceCorrection = AttendanceCorrection::where('request_batch_id', $requestBatchId)->first();
        $restCorrections = RestCorrection::where('request_batch_id', $requestBatchId)->get();

        $requestBatchId = $attendanceCorrection->request_batch_id ?? $restCorrections->first()->request_batch_id;

        $date = $attendanceCorrection->date ?? $restCorrections->first()->date;

        $userId = $attendanceCorrection->user_id ?? $restCorrections->first()->user_id;

        $attendance = Attendance::where('date', $date)->where('user_id', $userId)->first() ?? null;
        $rests = collect();

        // 承認・未承認ステータス定義
        $attendanceUnapproved = false;
        $attendanceApproved = false;
        $restUnapproved = false;
        $restApproved = false;

        if ($attendanceCorrection && $attendanceCorrection->status === '承認待ち') {
            $attendanceUnapproved = true;
        } elseif ($attendanceCorrection && $attendanceCorrection->status === '承認済み') {
            $attendanceApproved = true;
        }

        if ($attendance) {
            $rests = Rest::where('attendance_id', $attendance->id)->get() ?? collect();

            $displayRows = AttendanceService::mergeRestsAndRestCorrections($rests, $restCorrections);

            foreach ($restCorrections as $restCorrection) {
                if ($restCorrection->rest_id === !null) {
                    $restCorrections[$restCorrection->rest_id] = $restCorrection;
                }
                //休憩ごとに休憩申請を取得（上書きされない）
                if ($restCorrection && $restCorrection->status === '承認待ち') {
                    $restUnapproved = true;
                } elseif ($restCorrection && $restCorrection->status === '承認済み') {
                    $restApproved = true;
                }
            }
        } else {
            foreach ($restCorrections as $restCorrection) {
                $key = $restCorrection->rest_id ?? 0;
                $restCorrections[$key] = $restCorrection;
                if ($restCorrection && $restCorrection->status === '承認待ち') {
                    $restUnapproved = true;
                } elseif ($restCorrection && $restCorrection->status === '承認済み') {
                    $restApproved = true;
                }
            }
        }

        $unapprovedCorrections = $attendanceUnapproved || $restUnapproved;

        $approvedCorrections = $attendanceApproved || $restApproved;

        return view('admin.correction_detail', compact('requestBatchId', 'attendance', 'rests', 'attendanceCorrection', 'restCorrections', 'displayRows', 'unapprovedCorrections', 'approvedCorrections'));
    }
}
