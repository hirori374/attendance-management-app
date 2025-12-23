@extends('layouts.app')
@section('title','管理者ログイン')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('content')
<div class="login__container">
    <h2 class="form__title">管理者ログイン</h2>
    <form action="/admin/login" method="post" class="login-form">
        @csrf
        <div class="form-group">
            <div class="form-group__title">メールアドレス</div>
            <input type="email" name="email" value="{{ old('email') }}" class="form-group__content">
            <div class="form__error">
                @error('email')
                    {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form-group">
            <div class="form-group__title">パスワード</div>
            <input type="password" name="password" class="form-group__content">
            <div class="form__error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit">管理者ログインする</button>
        </div>
    </form>
</div>
@endsection