<x-layouts.admin title="LCPumps Admin — Дашборд">
    <h1 class="font-heading text-[28px] font-bold text-heading">Дашборд</h1>
    <p class="mt-2 text-muted">Обзор заявок и товаров каталога.</p>

    <div class="mt-8 grid grid-cols-1 gap-4 tablet:grid-cols-3">
        <x-admin.stat-card label="Всего заявок" :value="$totalRequests" />
        <x-admin.stat-card label="Новых заявок" :value="$newRequests" />
        <x-admin.stat-card label="Заявок сегодня" :value="$todayRequests" />
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 tablet:grid-cols-3">
        <x-admin.stat-card label="Всего товаров" :value="$totalProducts" />
    </div>

    <div class="mt-10 flex flex-wrap gap-4">
        <a
            href="{{ route('admin.requests.index') }}"
            class="rounded-sm bg-primary px-6 py-3.5 text-sm font-bold text-white no-underline shadow-btn transition-[background,transform] duration-200 hover:-translate-y-0.5 hover:bg-primary-dark"
        >
            Смотреть заявки
        </a>
        <a
            href="{{ route('admin.products.index') }}"
            class="rounded-sm border border-border-mid bg-white px-6 py-3.5 text-sm font-bold text-body no-underline transition-colors hover:border-primary hover:text-primary"
        >
            Редактировать товары
        </a>
    </div>
</x-layouts.admin>
