{{--
    Stage 7: functional only. Visual design belongs to Stage 8, which owns
    the admin panel's styling system — don't pre-empt it here.
--}}
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <title>LCPumps Admin — Вход</title>
    <style>
        body { font-family: sans-serif; display: flex; min-height: 100vh; align-items: center; justify-content: center; background: #f5f5f5; margin: 0; }
        .card { width: 100%; max-width: 360px; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 2rem; }
        h1 { font-size: 1.25rem; margin: 0 0 1.5rem; }
        label { display: block; font-size: 0.875rem; margin-bottom: 0.25rem; }
        input { width: 100%; box-sizing: border-box; padding: 0.6rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 0.7rem; background: #f70620; color: #fff; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .error { color: #c0392b; font-size: 0.875rem; margin: 0 0 1rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1>LCPumps Admin</h1>

        @if ($errors->has('login'))
            <p class="error">{{ $errors->first('login') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.login.attempt') }}">
            @csrf

            <label for="username">Логин</label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" autocomplete="username" autofocus>

            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" autocomplete="current-password">

            <button type="submit">Войти</button>
        </form>
    </div>
</body>
</html>
