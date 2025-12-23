<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/layouts/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    @yield('css')
    <title>@yield('title')</title>
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo"><img src="{{ asset('img/logo.svg') }}" alt="ロゴ画像"></div>
            <nav class="header__nav">
                @if (Auth::check() && Auth::user()->authority === "admin")
                    <ul class="header__nav-list">
                        <li class="header__nav-item">
                            <a href="{{ route('admin.attendance.list') }}" class="header__nav-link">勤怠一覧</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('admin.staff.list') }}" class="header__nav-link">スタッフ一覧</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('admin.correction.request.list', ['status' => 'unapproved']) }}" class="header__nav-link">申請一覧</a>
                        </li>
                        <li class="header__nav-item">
                            <form method="post" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="header__nav-link">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                @elseif (Auth::check() && Auth::user()->authority === "general")
                    <ul class="header__nav-list">
                        <li class="header__nav-item">
                            <a href="{{ route('attendance')}}" class="header__nav-link">勤怠</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('attendance.list') }}" class="header__nav-link">勤怠一覧</a>
                        </li>
                        <li class="header__nav-item">
                            <a href="{{ route('correction.request.list', ['status' => 'unapproved']) }}" class="header__nav-link">申請</a>
                        </li>
                        <li class="header__nav-item">
                            <form method="post" action="/logout">
                                @csrf
                                <button type="submit" class="header__nav-link">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                @endif
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>