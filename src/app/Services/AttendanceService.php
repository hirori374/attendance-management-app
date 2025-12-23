<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceService
{
    // 年月の指定
    public static function getTargetMonth(string $yearMonth, ?string $page = null) : Carbon
    {
        $carbon = Carbon::createFromFormat('Y-m', $yearMonth);

        if ($page === 'previous') {
            $carbon->subMonthNoOverflow();
        } elseif ($page === 'next') {
            $carbon->addMonthNoOverflow();
        }

        return $carbon;
    }
    // 年月日の指定(管理者日別勤怠一覧用)
    public static function getTargetDay(string $date, ?string $page = null) : Carbon
    {
        $carbon = Carbon::createFromFormat('Y-m-d', $date);

        if ($page === 'previous') {
            $carbon->subDay();
        } elseif ($page === 'next') {
            $carbon->addDay();
        }

        return $carbon;
    }
    // 指定年月の日付コレクション作成
    public static function generateMonthDates(Carbon $targetMonth): Collection
    {
        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        $dates = collect();
        for ($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay()) {
            $dates->push($date->copy());
        }

        return $dates;
    }
    // スタッフ勤怠情報取得
    public static function getMonthlyAttendance(int $userId, Carbon $targetMonth): Collection
    {
        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        return Attendance::where('user_id', $userId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->with('rests')
            ->get()
            ->keyBy(fn($attendance) => $attendance->date->format('Y-m-d'));
    }
    // RestとRestCorrectionの結合
    public static function mergeRestsAndRestCorrections(Collection $rests, Collection $restCorrections)
    {
        // restsの ID一覧
        $restIds = $rests->pluck('id')->all();
        // 新規restCorrections
        $standaloneRestCorrections = $restCorrections->filter(function($rc) use ($restIds) {
            return !in_array($rc->rest_id, $restIds);
        });

        $displayRows = collect();

        // ① Restに存在するデータ（主）
        foreach ($rests as $rest) {
            $displayRows->push([
                'rest' => $rest,
                'restCorrection' => $restCorrections->where('rest_id', $rest->id)->first(),
            ]);
        }
        // ② 孤立したRestCorrection（追加申請）
        foreach ($standaloneRestCorrections as $rc) {
            $displayRows->push([
                'rest' => null,
                'restCorrection' => $rc,
            ]);
        }

        return $displayRows;
    }
    // CSV出力処理
    public static function exportMonthlyCsv(User $user, Carbon $targetMonth, Collection $attendances): StreamedResponse
    {
        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();
        $dates = self::generateMonthDates($targetMonth);

        $fileName = 'attendance_' . $targetMonth . '_' . $user->name . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=Shift_JIS',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($dates, $user, $attendances) {
            // Shift-JIS で出力するため変換
            $stream = fopen('php://output', 'w');
            // Excel で文字化けしないよう BOM
            fprintf($stream, chr(0xEF).chr(0xBB).chr(0xBF));
            // ヘッダ行
            fputcsv($stream, ['名前', '日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($dates as $date) {
                $dateKey = $date->format('Y-m-d');
                $attendance = $attendances->get($dateKey);

                if ($attendance) {
                    $start = Carbon::createFromFormat('H:i:s', $attendance->attendance_start_time);
                    $end = Carbon::createFromFormat('H:i:s', $attendance->attendance_end_time);

                    if ($start && $end) {
                        $totalWorkMinutes = $end->diffInMinutes($start);
                        $totalRestMinutes = $attendance->rests->sum(function ($rest) {
                            $restStart = Carbon::createFromFormat('H:i:s', $rest->rest_start_time);
                            $restEnd = Carbon::createFromFormat('H:i:s', $rest->rest_end_time);
                            return $rest->rest_end_time
                                ? $restEnd->diffInMinutes($restStart)
                                : 0;
                        });

                        $workMinutes = $totalWorkMinutes - $totalRestMinutes;
                    } else {
                        $totalRestMinutes = 0;
                        $workMinutes = null;
                    }

                    fputcsv($stream, [
                        $user->name,
                        $date->format('Y/m/d'),
                        $start ? $start->format('H:i') : '',
                        $end ? $end->format('H:i') : '',
                        $totalRestMinutes ? gmdate('H:i', $totalRestMinutes * 60) : '',
                        $workMinutes !== null ? gmdate('H:i', $workMinutes * 60) : '',
                    ]);
                } else {
                    fputcsv($stream, [
                        $user->name,
                        $date->format('Y/m/d'),
                        '', '', '', '',
                    ]);
                }
            }

            fclose($stream);
        };
        return new StreamedResponse($callback, 200, $headers);
    }
}