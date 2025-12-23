@extends('layouts.app')
@section('title','スタッフ一覧')
@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="list">
    <div class="list__inner">
        <h2 class="list__title">スタッフ一覧</h2>
        <table class="list__table">
            <tr class="table__header">
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤務</th>
            </tr>
            @foreach ($users as $user)
            <tr class="table__data">
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td><a href="{{ route('admin.attendance.staff', ['userId' => $user->id]) }}" class="detail-link">詳細</a></td>
            </tr>
            @endforeach
        </table>
    </div>
</div>

@endsection