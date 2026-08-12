{{--
    Stage 7: bare placeholder proving the guard/session work. Real content
    (product/request summaries etc.) and styling are Stage 8.
--}}
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex, nofollow">
    <title>LCPumps Admin</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; }
        button { padding: 0.5rem 1rem; cursor: pointer; }
    </style>
</head>
<body>
    <p>Вы вошли как <strong>{{ $username }}</strong>.</p>

    <form method="POST" action="{{ route('admin.logout') }}">
        @csrf
        <button type="submit">Выйти</button>
    </form>
</body>
</html>
