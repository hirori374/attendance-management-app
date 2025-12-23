@extends('layouts.app')
@section('title', '日別勤怠')
@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="list">
        <div class="list__inner">
            <h2 class="list__title">{{ $titleDate }}の勤怠</h2>
            <div class="list__index">
                <a href="{{ route('admin.attendance.list', ['date' => $date, 'page' => 'previous']) }}" class="page__link previous-month">前日</a>
                <div class="calendar">
                    <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="calendar__img">
                    <div class="calendar__selected">{{ $indexDate }}</div>
                </div>
                <a href="{{ route('admin.attendance.list', ['date' => $date, 'page' => 'next']) }}" class="page__link next-month">翌日</a>
            </div>
            <table class="list__table">
                <tr class="table__header">
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                @foreach ($attendances as $attendance)
                    <tr class="table__data" data-attendance-id="{{ $attendance->id }}">
                        <td>{{ $attendance->user->name }}</td>
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
                            <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @endforeach

            </table>
        </div>
    </div>

@endsection