{{--
    Ported from components/admin/AdminShell.tsx — same nav items, same
    tokens as the public layout (components/layouts/app.blade.php), but a
    separate component: admin has no header/footer partials, no locale
    switcher, no <html lang> variance (Russian-only, see the Stage 7 brief).
--}}
@php
    $username = auth('admin')->user()->username;
    $navItems = [
        ['route' => 'admin.dashboard', 'label' => 'Дашборд', 'active' => request()->routeIs('admin.dashboard')],
        ['route' => 'admin.requests.index', 'label' => 'Заявки', 'active' => request()->routeIs('admin.requests.*')],
        ['route' => 'admin.products.index', 'label' => 'Товары', 'active' => request()->routeIs('admin.products.*')],
    ];
@endphp
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'LCPumps Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ Vite::fonts() }}
</head>
<body class="bg-surface">
    <div class="flex min-h-dvh">
        <aside class="flex w-64 shrink-0 flex-col border-r border-border bg-white max-tablet:hidden">
            <div class="border-b border-border-light px-6 py-6">
                <div class="text-xs font-bold uppercase tracking-[1.4px] text-primary">LCPumps</div>
                <div class="font-heading text-lg font-bold text-heading">Admin</div>
            </div>

            <nav class="flex-1 px-3 py-4">
                @foreach ($navItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="mb-1 flex items-center gap-3 rounded-md px-3.5 py-2.5 text-sm font-semibold no-underline transition-colors {{ $item['active'] ? 'bg-pill-blue-bg text-primary' : 'text-nav hover:bg-surface hover:text-primary' }}"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-border-light p-3">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="flex w-full cursor-pointer items-center gap-3 rounded-md border-none bg-transparent px-3.5 py-2.5 text-left text-sm font-semibold text-muted transition-colors hover:bg-surface hover:text-error"
                    >
                        Выйти
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center justify-between border-b border-border-light bg-white px-6 py-4 tablet:px-8">
                <span class="font-heading text-lg font-bold text-heading tablet:hidden">LCPumps Admin</span>
                <span class="hidden tablet:block"></span>
                <span class="inline-flex items-center gap-2 rounded-pill bg-pill-blue-bg px-3.5 py-1.5 text-xs font-bold uppercase tracking-wide text-primary">
                    {{ $username }}
                </span>
            </header>
            <main class="flex-1 px-6 py-8 tablet:px-10 tablet:py-10">
                @if (session('admin_success'))
                    <div class="mb-6 rounded-md border border-[#bfe3cd] bg-[#eafaf1] px-4 py-3 text-sm font-semibold text-success">
                        {{ session('admin_success') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
