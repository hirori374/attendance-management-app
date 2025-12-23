@extends('layouts.app')
@section('title', '申請一覧')
@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
    <div class="list">
        <div class="list__inner">
            <h2 class="list__title">申請一覧</h2>
            <div class="list__tab">
                @if (Auth::check() && Auth::user()->authority === 'admin')
                    <a href="{{ route('admin.correction.request.list', ['status' => 'unapproved']) }}" class="tab__link {{ request('status') === 'unapproved' ? 'active' : '' }}">承認待ち</a>
                    <a href="{{ route('admin.correction.request.list', ['status' => 'approved']) }}" class="tab__link {{ request('status') === 'approved' ? 'active' : '' }}">承認済み</a>
                @elseif (Auth::check() && Auth::user()->authority === 'general')
                    <a href="{{ route('correction.request.list', ['status' => 'unapproved']) }}" class="tab__link {{ request('status') === 'unapproved' ? 'active' : '' }}">承認待ち</a>
                    <a href="{{ route('correction.request.list', ['status' => 'approved']) }}" class="tab__link {{ request('status') === 'approved' ? 'active' : '' }}">承認済み</a>
                @endif
            </div>
            <table class="list__table">
                <tr class="table__header">
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
                @foreach ($groupedCorrections as $correction)
                    <tr class="table__data" request-batch-id="{{ $correction['request_batch_id'] }}">
                        <td>{{ $correction['status'] }}</td>
                        <td>{{ $correction['user_name'] }}</td>
                        <td>{{ $correction['date']->format('Y/m/d') }}</td>
                        <td>{{ $correction['remarks'] }}</td>
                        <td>{{ $correction['request_date']->format('Y/m/d') }}</td>
                        <td>
                            @if (Auth::check() && Auth::user()->authority === "admin")
                                <a href="{{ route('admin.request.detail', ['requestBatchId' => $correction['request_batch_id']]) }}"  class="detail-link">詳細</a>
                            @elseif (Auth::check() && Auth::user()->authority === 'general')
                                <a href="{{ route('correction.request.detail', ['requestBatchId' => $correction['request_batch_id']]) }}" class="detail-link">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

@endsection