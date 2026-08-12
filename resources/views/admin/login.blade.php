{{-- Ported from app/admin/login/page.tsx — same layout/tokens, plain POST form instead of a client Server Action. --}}
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>LCPumps Admin — Вход</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ Vite::fonts() }}
</head>
<body class="bg-surface">
    <div class="flex min-h-dvh items-center justify-center px-6 py-12">
        <div class="w-full max-w-[380px] rounded-2xl border border-border-mid bg-white p-8 shadow-card-sm">
            <div class="mb-6 text-center">
                <div class="mb-1 text-xs font-bold uppercase tracking-[1.4px] text-primary">LCPumps Admin</div>
                <h1 class="font-heading text-2xl font-bold text-heading">Вход</h1>
            </div>

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="flex flex-col gap-4">
                @csrf

                <input
                    class="w-full rounded-md border border-border-mid bg-white px-4 py-3.5 text-[15px] text-body placeholder:text-subtle focus:border-primary focus:shadow-[0_0_0_3px_rgba(247,6,32,0.12)] focus:outline-none"
                    type="text"
                    name="username"
                    placeholder="Логин"
                    value="{{ old('username') }}"
                    autocomplete="username"
                    autofocus
                >
                <input
                    class="w-full rounded-md border border-border-mid bg-white px-4 py-3.5 text-[15px] text-body placeholder:text-subtle focus:border-primary focus:shadow-[0_0_0_3px_rgba(247,6,32,0.12)] focus:outline-none"
                    type="password"
                    name="password"
                    placeholder="Пароль"
                    autocomplete="current-password"
                >

                @if ($errors->has('login'))
                    <p class="text-sm font-semibold text-error">{{ $errors->first('login') }}</p>
                @endif

                <button
                    type="submit"
                    class="mt-2 w-full rounded-sm bg-primary py-3.5 text-base font-bold text-white shadow-btn transition-[background,transform] duration-200 hover:-translate-y-0.5 hover:bg-primary-dark"
                >
                    Войти
                </button>
            </form>
        </div>
    </div>
</body>
</html>
