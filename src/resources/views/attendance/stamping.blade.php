@extends('layouts.app')
@section('title','勤怠')
@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="stamping">
    <div class="stamping__status">{{ $status }}</div>
    <div class="stamping__information">
        <div class="stamping__date">{{ $today }}</div>
        <div class="stamping__time">{{ $time }}</div>
    </div>
    <div class="stamping__button">
        @if ($status === '勤務外')
        <form action="{{ route('attendance.start') }}" method="post" class="stamping__form">
            @csrf
            <button class="stamping__button-submit button--attendance">出勤</button>
        </form>
        @elseif ($status === '出勤中')
        <form action="{{ route('attendance.end') }}" method="post" class="stamping__form">
            @csrf
            <button class="stamping__button-submit button--attendance">退勤</button>
        </form>
        <form action="{{ route('rest.start') }}" method="post" class="stamping__form">
            @csrf
            <button class="stamping__button-submit button--rest">休憩入</button>
        </form>
        @elseif ($status === '休憩中')
        <form action="{{ route('rest.end') }}" method="post" class="stamping__form">
            @csrf
            <button class="stamping__button-submit button--rest" formaction="/rest/end">休憩戻</button>
        </form>
        @elseif ($status === '退勤済')
        <p class="attendance-end__message">お疲れ様でした。</p>
        @endif
    </div>
</div>

@endsection