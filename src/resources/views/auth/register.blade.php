@extends('layouts.app')
@section('title','会員登録')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('content')
<div class="register__container">
    <h2 class="form__title">会員登録</h2>
    <form action="/register" method="post" class="register-form">
        @csrf
        <div class="form-group">
            <div class="form-group__title">名前</div>
            <input type="text" name="name" value="{{ old('name') }}" class="form-group__content">
            <div class="form__error">
                @error('name')
                    {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form-group">
            <div class="form-group__title">メールアドレス</div>
            <input type="email" name="email" value="{{ old('email') }} "class="form-group__content">
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
        <div class="form-group">
            <div class="form-group__title">パスワード確認</div>
            <input type="password" name="password_confirmation" class="form-group__content">
            <div class="form__error">
                @error('password')
                    {{ $message }}
                @enderror
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit">登録する</button>
            <a href="/login" class="login__link">ログインはこちら</a>
        </div>
    </form>
</div>
@endsection