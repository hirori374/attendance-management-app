<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrection;
use App\Models\RestCorrection;
use App\Http\Requests\CorrectionRequest;
use App\Services\AttendanceService;

class CorrectionController extends Controller
{
    // 勤怠修正処理
    public function correction(CorrectionRequest $request, $id)
    {
        $attendance = Attendance::find($id);
        $rests = Rest::where('attendance_id', $id)->get();

        $attendanceStartTimeInput = $request->attendance_start_time ? $request->attendance_start_time . ':00' : null;
        $attendanceEndTimeInput = $request->attendance_end_time ? $request->attendance_end_time . ':00' : null;

        //入力値が元のデータの値と違っていたら更新
        // attendance
        if (optional($attendance->start_time)?->format('H:i:s') !== $attendanceStartTimeInput || optional($attendance->attendance_end_time)?->format('H:i:s') !== $attendanceEndTimeInput)
        {
            $attendance->update([
                'attendance_start_time' => $attendanceStartTimeInput,
                'attendance_end_time' => $attendanceEndTimeInput,
                'remarks' => $request->input('remarks'),
            ]);
        }

        // rests
        foreach ($rests as $index => $rest) {
            $rest = $rests[$index] ?? null;
            $restStartTimeInput = $request->input('rest_start_time')[$index] ?? null;
            $restEndTimeInput = $request->input('rest_end_time')[$index] ?? null;

            if ($restStartTimeInput) {
                $restStartTimeInput = $restStartTimeInput ? $restStartTimeInput . ':00' : null;
            }
            if ($restEndTimeInput) {
                $restEndTimeInput = $restEndTimeInput ? $restEndTimeInput . ':00' : null;
            }

            if ($rest->rest_start_time !== $restStartTimeInput || $rest->rest_end_time !== $restEndTimeInput) {
                $rest->update([
                    'rest_start_time' => $restStartTimeInput,
                    'rest_end_time' => $restEndTimeInput,
                    'remarks' => $request->input('remarks'),
                ]);
            }
        }

        $newRestIndex = $rests->count() ?? 0;
        if($request->input('rest_start_time')[$newRestIndex] !== null || $request->input('rest_end_time')[$newRestIndex] !== null){
            $newRestStartTimeInput = $request->input('rest_start_time')[$newRestIndex] ?? null;
            $newRestEndTimeInput = $request->input('rest_end_time')[$newRestIndex] ?? null;
            if ($newRestStartTimeInput) {
                $newRestStartTimeInput = $newRestStartTimeInput ? $newRestStartTimeInput . ':00' : null;
            }
            if ($newRestEndTimeInput) {
                $newRestEndTimeInput = $newRestEndTimeInput ? $newRestEndTimeInput . ':00' : null;
            }

            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_start_time' => $newRestStartTimeInput,
                'rest_end_time' => $newRestEndTimeInput,
                'remarks' => $request->input('remarks'),
            ]);
        }

        return redirect()->route('admin.attendance.detail', ['id' => $id]);
    }
    // 勤怠新規作成
    public function attendanceCreate(CorrectionRequest $request)
    {
        $userId = $request->query('userId');
        $attendanceStartTimeInput = $request->attendance_start_time ? $request->attendance_start_time . ':00' : null;
        $attendanceEndTimeInput = $request->attendance_end_time ? $request->attendance_end_time . ':00' : null;

        $attendance = Attendance::create([
            'user_id' => $userId,
            'date' => $request->input('date'),
            'attendance_start_time' => $attendanceStartTimeInput,
            'attendance_end_time' => $attendanceEndTimeInput,
            'remarks' => $request->input('remarks'),
        ]);

        $restStartTimeInput = $request->input('rest_start_time') ?? null;
        $restEndTimeInput = $request->input('rest_end_time') ?? null;

        if (is_string($restStartTimeInput)) {
            $restStartTimeInput = trim($restStartTimeInput);

            if ($restStartTimeInput === '') {
                $restStartTimeInput = null;
            }
        }
        try {
            if ($restStartTimeInput !== null) {
                $restStartTimeInput = $restStartTimeInput ? $restStartTimeInput . ':00' : null;
            }
        } catch (\Exception $e) {
            $restStartTimeInput = null;
        }
        if (is_string($restEndTimeInput)) {
            $restEndTimeInput = trim($restEndTimeInput);

            if ($restEndTimeInput === '') {
                $restEndTimeInput = null;
            }
        }
        try {
            if ($restEndTimeInput !== null) {
                $restEndTimeInput = $restEndTimeInput ? $restEndTimeInput . ':00' : null;
            }
        } catch (\Exception $e) {
            $restEndTimeInput = null;
        }

        if ($restStartTimeInput || $restEndTimeInput !== null) {
            Rest::create([
                'attendance_id' => $attendance->id,
                'rest_start_time' => $restStartTimeInput,
                'rest_end_time' => $restEndTimeInput,
                'remarks' => $request->input('remarks'),
            ]);
        }

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
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

        // 承認ステータス
        $status = $request->query('status');
        if ($status === 'unapproved') {
            $groupedCorrections = $groupedCorrections->where('status', '承認待ち');
        } elseif ($status === 'approved') {
            $groupedCorrections = $groupedCorrections->where('status', '承認済み');
        }

        return view('correction.list', compact('groupedCorrections'));
    }
    // 修正依頼の詳細表示
    public function requestDetail($requestBatchId)
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
                if($restCorrection->rest_id === !null){
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
            foreach ($restCorrections as $restCorrection){
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

        return view('admin.correction_detail', compact('requestBatchId', 'attendance', 'rests', 'attendanceCorrection', 'restCorrections', 'displayRows',  'unapprovedCorrections', 'approvedCorrections'));
    }
    // 修正依頼の承認
    public function approve(Request $request, $requestBatchId)
    {
        // 修正申請情報取得、勤怠・休憩情報取得
        $attendanceCorrection = AttendanceCorrection::where('request_batch_id', $requestBatchId)->first();
        $restCorrections = RestCorrection::where('request_batch_id', $requestBatchId)->get();
        $userId = $attendanceCorrection->user_id ?? $restCorrections->first()->user_id;
        $date = $attendanceCorrection->date ?? $restCorrections->first()->date;

        $attendance = Attendance::where('user_id', $userId)->where('date', $date)->first() ?? null;
        $rests = collect();

        $attendanceStartTimeRequest = $attendanceCorrection?->attendance_request_start_time;
        $attendanceEndTimeRequest = $attendanceCorrection?->attendance_request_end_time;

        // 勤怠情報がある場合
        if ($attendance) {
            // 勤怠修正申請あり→更新
            if ($attendanceCorrection){
                $attendance->update([
                    'attendance_start_time' => $attendanceStartTimeRequest,
                    'attendance_end_time' => $attendanceEndTimeRequest,
                    'remarks' => $attendanceCorrection->remarks,
                ]);
                $attendanceCorrection->update([
                    'status' => '承認済み'
                ]);
            }

            // 休憩修正申請あり→更新・作成
            $rests = Rest::where('attendance_id', $attendance->id)->get();
            foreach ($restCorrections as $restCorrection) {
                if ($restCorrection->rest_id) {
                    $rest = Rest::find($restCorrection->rest_id);
                    if ($rest) {
                        $rest->update([
                            'rest_start_time' => $restCorrection->rest_request_start_time,
                            'rest_end_time' => $restCorrection->rest_request_end_time,
                            'remarks' => $restCorrection->remarks,
                        ]);
                    }
                }
                else {
                    $rest = Rest::create([
                        'attendance_id' => $attendance->id,
                        'rest_start_time' => $restCorrection->rest_request_start_time,
                        'rest_end_time' => $restCorrection->rest_request_end_time,
                        'remarks' => $restCorrection->remarks,
                    ]);

                    $restCorrection->update(['rest_id' => $rest->id]);
                }

                $attendance->update(['remarks' => $restCorrection->remarks]);

                $restCorrection->update(['status' => '承認済み']);
            }
        }

        // 勤怠情報がない場合
        if (!$attendance) {
            // 勤怠情報新規作成
            $attendance = Attendance::create([
                'user_id' => $userId,
                'date' => $date,
                'attendance_start_time' => $attendanceStartTimeRequest,
                'attendance_end_time' => $attendanceEndTimeRequest,
                'remarks' => $attendanceCorrection->remarks,
            ]);

            $attendanceCorrection->update(['attendance_id' => $attendance->id]);

            $attendanceCorrection->update([
                'status' => '承認済み'
            ]);

            // 休憩修正申請あり→休憩情報新規作成
            foreach ($restCorrections as $restCorrection) {
                $rest = Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_start_time' => $restCorrection->rest_request_start_time,
                    'rest_end_time' => $restCorrection->rest_request_end_time,
                    'remarks' => $restCorrection->remarks,
                ]);

                $restCorrection->update(['rest_id' => $rest->id]);

                $restCorrection->update(['status' => '承認済み']);
            }
        }

        return redirect()->route('admin.request.detail', ['requestBatchId' => $requestBatchId]);
    }
}
