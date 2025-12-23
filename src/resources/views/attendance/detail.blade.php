@extends('layouts.app')
@section('title','勤怠詳細')
@section('css')
<link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection
@section('content')
    <div class="attendance__detail">
        <div class="detail__inner">
            <h2 class="detail__title">勤怠詳細</h2>
            @if ($unapprovedCorrections)
                <table class="detail__table">
                    <tr class="table__row">
                        <th>名前</th>
                        <td>
                            <input type="text" class="name__input" name="name" value="{{ $user->name }}" readonly />
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
                            <p class="readonly no-left-margin">{{ $attendanceCorrection?->attendance_request_start_time_formatted ?? $attendance?->attendance_start_time_formatted }}</p>
                            <span>〜</span>
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
                                <p class="readonly no-left-margin">{{ $restCorrection?->rest_request_start_time_formatted ?? $rest?->rest_start_time_formatted ?? '' }}</p>
                                <span>〜</span>
                                <p class="readonly">{{ $restCorrection?->rest_request_end_time_formatted ?? $rest?->rest_end_time_formatted ?? '' }}</p>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="table__row">
                        <th>備考</th>
                        <td><p class="readonly--remarks">{{ $attendanceCorrection?->remarks ?? $restCorrection?->remarks ?? '' }}</p></td>
                    </tr>
                </table>
                <p class="unable__text">*承認待ちのため修正はできません。</p>
            @elseif ($attendance)
                <form action="{{ route('correction.request', ['id' => $attendance->id])}}" method="post" class="correction-request__form">
                    @csrf
                    <table class="detail__table">
                        <tr class="table__row">
                            <th>名前</th>
                            <td>
                                <input type="text" class="name__input" name="name" value="{{ $user->name }}" readonly />
                            </td>
                        </tr>
                        <tr class="table__row">
                            <th>日付</th>
                            <td>
                                <input type="text" class="date__input" name="year" value="{{ $attendance->date->translatedFormat('Y年') }}" readonly />
                                <input type="text" class="date__input" name="date" value="{{ $attendance->date->translatedFormat('m月d日') }}" readonly />
                            </td>
                        </tr>
                        <tr class="table__row">
                            <th>出勤・退勤</th>
                            <td>
                                <input type="time" name="attendance_start_time" class="time__input" value="{{ old('attendance_start_time', $attendance->attendance_start_time_formatted ?? '') }}">
                                <span>〜</span>
                                <input type="time" name="attendance_end_time" class="time__input" value="{{ old('attendance_end_time', $attendance->attendance_end_time_formatted ?? '') }}">
                                @if ($errors->has('attendance_start_time') || $errors->has('attendance_end_time'))
                                    <div class="form__error">
                                        @error('attendance_start_time'){{ $message }}@enderror
                                        @error('attendance_end_time'){{ $message }}@enderror
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @php
                            $totalRows = $rests->count() > 0 ? $rests->count() + 1 : 1;
                        @endphp
                        @for ($index = 0; $index < $totalRows; $index++)
                            @php
                                $rest = $rests[$index] ?? null;
                            @endphp
                            <tr class="table__row">
                                <th>休憩{{ $index + 1 > 1 ? $index + 1 : '' }}</th>
                                <td>
                                    <input type="time" name="rest_start_time[]" class="time__input" value="{{ old('rest_start_time.' . $index, $rest?->rest_start_time_formatted ?? '' )}}">
                                    <span>〜</span>
                                    <input type="time" name="rest_end_time[]" class="time__input" value="{{ old('rest_end_time.' . $index, $rest?->rest_end_time_formatted ?? '' )}}">
                                    @if ($errors->has("rest_start_time.$index") || $errors->has("rest_end_time.$index"))
                                        <div class="form__error">
                                            @error("rest_start_time.$index"){{ $message }}@enderror
                                            @error("rest_end_time.$index"){{ $message }}@enderror
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endfor
                        <tr class="table__row">
                            <th>備考</th>
                            <td>
                                <textarea name="remarks" id="" class="remarks__textarea">{{ old('remarks', $attendance->remarks ?? $rest?->remarks ?? '') }}</textarea>
                                @if ($errors->has('remarks'))
                                    <div class="form__error">
                                        @error('remarks'){{ $message }}@enderror
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                    <div class="form__button">
                        <button class="submit">修正</button>
                    </div>
                </form>
            @elseif(!$attendance)
                <form action="{{ route('create.request')}}" method="post" class="correction-request__form">
                    @csrf
                    <table class="detail__table">
                        <tr class="table__row">
                            <th>名前</th>
                            <td>
                                <input type="text" class="name__input" name="name" value="{{ $user->name }}" readonly />
                            </td>
                        </tr>
                        <tr class="table__row">
                            <th>日付</th>
                            <td>
                                <input type="text" class="date__input" value="{{ $date->translatedFormat('Y年') }}" readonly />
                                <input type="text" class="date__input" value="{{ $date->translatedFormat('m月d日') }}" readonly />
                                <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                            </td>
                        </tr>
                        <tr class="table__row">
                            <th>出勤・退勤</th>
                            <td>
                                <input type="time" name="attendance_start_time" class="time__input" value="{{ old('attendance_start_time') }}">
                                <span>〜</span>
                                <input type="time" name="attendance_end_time" class="time__input" value="{{ old('attendance_end_time') }}">
                                @if ($errors->has('attendance_start_time') || $errors->has('attendance_end_time'))
                                    <div class="form__error">
                                        @error('attendance_start_time'){{ $message }}@enderror
                                    </div>
                                    <div class="form__error">
                                        @error('attendance_end_time'){{ $message }}@enderror
                                    </div>
                                @endif
                            </td>
                        </tr>
                        <tr class="table__row">
                            <th>休憩</th>
                            <td>
                                <input type="time" name="rest_start_time" class="time__input" value="{{ old('rest_start_time') }}">
                                <span>〜</span>
                                <input type="time" name="rest_end_time" class="time__input" value="{{ old('rest_end_time') }}}">
                                @if ($errors->has('rest_start_time') || $errors->has('rest_end_time'))
                                    <div class="form__error">
                                        @error("rest_start_time"){{ $message }}@enderror
                                        @error("rest_end_time"){{ $message }}@enderror
                                    </div>
                                @endif
                            </td>
                        </tr>
                        <tr class="table__row">
                            <th>備考</th>
                            <td>
                                <textarea name="remarks" id="" class="remarks__textarea">{{ old('remarks') ?? '' }}</textarea>
                                @if ($errors->has('remarks'))
                                    <div class="form__error">
                                        @error('remarks'){{ $message }}@enderror
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                    <div class="form__button">
                        <button class="submit">修正</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endsection