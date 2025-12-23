@extends('layouts.app')
@section('title','修正申請詳細')
@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection
@section('content')
    <div class="attendance__detail">
        <div class="detail__inner">
            <h2 class="detail__title">勤怠詳細</h2>
            <form action="{{ route('admin.approve', ['requestBatchId' => $requestBatchId])}}" method="post" class="approve__form">
                @csrf
                <table class="detail__table">
                    <tr class="table__row">
                        <th>名前</th>
                        <td>
                            <input type="text" class="name__input" name="name" value="{{ $attendance?->user->name ?? $attendanceCorrection->user->name }}" readonly />
                        </td>
                    </tr>
                    <tr class="table__row">
                        <th>日付</th>
                        <td>
                            <input type="text" class="date__input" name="year" value="{{ $attendance?->date->translatedFormat('Y年') ?? $attendanceCorrection->date->translatedFormat('Y年') }}" readonly />
                            <input type="text" class="date__input" name="date" value="{{ $attendance?->date->translatedFormat('m月d日') ?? $attendanceCorrection->date->translatedFormat('m月d日') }}" readonly />
                        </td>
                    </tr>
                    <tr class="table__row">
                        <th>出勤・退勤</th>
                        <td>
                            <input type="hidden" name="attendance_start_time" value="{{ $attendanceCorrection?->attendance_request_start_time_formatted ?? $attendance?->attendance_start_time_formatted }}">
                            <p class="readonly no-left-margin">{{ $attendanceCorrection?->attendance_request_start_time_formatted ?? $attendance?->attendance_start_time_formatted }}</p>
                            <span>〜</span>
                            <input type="hidden" name="attendance_end_time" value="{{ $attendanceCorrection?->attendance_request_end_time_formatted ?? $attendance?->attendance_end_time_formatted }}" class="readonly">
                            <p class="readonly">{{ $attendanceCorrection?->attendance_request_end_time_formatted ?? $attendance?->attendance_end_time_formatted }}</p>
                        </td>
                    </tr>
                    @foreach ($displayRows as $i => $row)
                        @php
                            $rest = $row['rest'];
                            $restCorrection = $row['restCorrection'];
                        @endphp
                        <tr class="table__row">
                            <th>休憩{{ $i + 1 > 1 ? $i + 1 : '' }}</th>
                            <td>
                                <input type="hidden" name="rest_start_time" value="{{ $restCorrection?->rest_request_start_time_formatted ?? $rest?->rest_start_time_formatted ?? '' }}">
                                <p class="readonly no-left-margin">{{ $restCorrection?->rest_request_start_time_formatted ?? $rest?->rest_start_time_formatted ?? '' }}</p>
                                <span>〜</span>
                                <input type="hidden" name="rest_end_time" value="{{ $restCorrection?->rest_request_end_time_formatted ?? $rest?->rest_end_time_formatted ?? '' }}">
                                <p class="readonly">{{ $restCorrection?->rest_request_end_time_formatted ?? $rest?->rest_end_time_formatted ?? '' }}</p>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="table__row">
                        <th>備考</th>
                        <td><p class="readonly--remarks">{{ $attendanceCorrection?->remarks ?? $restCorrection?->remarks ?? '' }}</p></td>
                    </tr>
                </table>
                <div class="form__button">
                    @if ($unapprovedCorrections)
                        <button class="submit">承認</button>
                    @else
                        <p class="approved__button">承認済み</p>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection