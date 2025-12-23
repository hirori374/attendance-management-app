@extends('layouts.app')
@section('title','スタッフ別勤怠一覧')
@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list">
    <div class="list__inner">
        <h2 class="list__title">{{ $user->name }}さんの勤怠一覧</h2>
        <div class="list__index">
            <a href="{{ route('admin.attendance.staff', ['userId' => $user->id, 'yearMonth' => $targetMonth->format('Y-m'), 'page' => 'previous']) }}" class="page__link previous-month">前月</a>
            <div class="calendar">
                <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="calendar__img">
                <div class="calendar__selected">{{ $displayYearMonth }}</div>
            </div>
            <a href="{{ route('admin.attendance.staff', ['userId' => $user->id, 'yearMonth' => $targetMonth->format('Y-m'), 'page' => 'next']) }}" class="page__link next-month">翌月</a>
        </div>
        <table class="list__table">
            <tr class="table__header">
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            @foreach ($dates as $date)
            @php
                $dateKey = $date->format('Y-m-d');
                $attendance = $attendances->get($dateKey);
            @endphp
            <tr class="table__data" data-date="{{ $date->format('Y-m-d') }}">
                <td>{{ $date->translatedFormat('m/d(D)') }}</td>
                <td>{{ $attendance?->attendance_start_time_formatted ?? '' }}</td>
                <td>{{ $attendance?->attendance_end_time_formatted ?? '' }}</td>
                <td>
                    @if ($attendance?->total_rest_time)
                        {{ floor($attendance->total_rest_time / 60) }}:{{ str_pad($attendance->total_rest_time % 60, 2, '0', STR_PAD_LEFT) }}
                    @endif
                </td>
                <td>
                    @if ($attendance?->work_minutes)
                        {{ floor($attendance->work_minutes / 60) }}:{{ str_pad($attendance->work_minutes % 60, 2, '0', STR_PAD_LEFT) }}
                    @endif
                </td>
                <td>
                    @if ($attendance)
                        <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id, 'date' => $dateKey]) }}" class="detail-link">詳細</a>
                    @else
                        <a href="{{ route('admin.attendance.detail', ['userId' => $user->id, 'date' => $dateKey]) }}" class="detail-link">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </table>
        <div class="export">
            <a href="{{ route('admin.attendance.export', ['userId' => $user->id, 'yearMonth' => $targetMonth->format('Y-m')])}}"  class="export__button">CSV出力</a>
        </div>
    </div>
</div>
@endsection