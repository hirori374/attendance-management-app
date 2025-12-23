@extends('layouts.app')
@section('title','会員登録完了')
@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify_email.css') }}">
@section('content')
<div class="mail-auth__inner">
    <div class="send__message">
        <div>登録していただいたメールアドレスに認証メールを送付しました。</br>メール認証を完了させてください。</div>
    </div>
    <form action="http://localhost:8025/" method="get" class="auth-mail__button">
        @csrf
        <button class="auth-mail__button-submit">認証はこちらから</button>
    </form>
    <form action="/email/verification-notification" method="post"  class="mail-resend__button">
        @csrf
        <button class="mail-resend__button-submit">認証メールを再送する</button>
    </form>
</div>
@endsection