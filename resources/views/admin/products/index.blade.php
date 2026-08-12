{{-- Ported from app/admin/(protected)/products/page.tsx + components/admin/ProductAdminTable.tsx. Search is a server round-trip (?q=) instead of client-side filtering. --}}
<x-layouts.admin title="LCPumps Admin — Товары">
    <h1 class="font-heading text-[28px] font-bold text-heading">Товары</h1>
    <p class="mt-2 text-muted">
        Редактирование, создание и удаление товаров каталога — название, описание, характеристики,
        категория, секция и изображение, отдельно для каждого языка.
    </p>

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.products.index') }}" class="max-w-96 flex-1">
            <input
                class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body placeholder:text-subtle focus:border-primary focus:shadow-[0_0_0_3px_rgba(247,6,32,0.12)] focus:outline-none"
                type="text"
                name="q"
                value="{{ $query }}"
                placeholder="Поиск по модели или названию…"
            >
        </form>
        <a
            href="{{ route('admin.products.create') }}"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-sm bg-primary px-4 py-2.5 text-sm font-bold text-white no-underline shadow-btn transition-[background,transform] duration-200 hover:-translate-y-0.5 hover:bg-primary-dark"
        >
            + Добавить товар
        </a>
    </div>

    <div class="mt-5 overflow-hidden rounded-2xl border border-border bg-white">
        @if ($products->isEmpty())
            <p class="px-5 py-8 text-center text-sm text-muted">Ничего не найдено.</p>
        @else
            @foreach ($products as $product)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 border-b border-border-light px-5 py-3.5 last:border-b-0">
                    @if ($product->image)
                        <img
                            src="{{ $product->image }}"
                            alt=""
                            class="size-12 shrink-0 rounded-md border border-border-mid bg-white object-contain p-1"
                        >
                    @else
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-md border border-border-mid bg-surface text-[10px] text-subtle">
                            нет фото
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-bold text-body">{{ $product->ru_name }}</div>
                        <div class="mt-0.5 text-xs text-subtle">{{ $product->code }} · {{ $categoryLabels[$product->category] ?? $product->category }}</div>
                    </div>
                    <div class="flex shrink-0 flex-col items-end gap-1">
                        <span class="text-sm font-bold text-body">{{ format_tenge($product->price) }}</span>
                        <span class="rounded-pill px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $product->stock_quantity > 0 ? 'bg-surface-alt text-success' : 'bg-surface-alt text-error' }}">
                            {{ $product->stock_quantity > 0 ? "В наличии: {$product->stock_quantity}" : 'Нет в наличии' }}
                        </span>
                    </div>
                    <a
                        href="{{ route('admin.products.edit', ['slug' => $product->slug]) }}"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-sm border border-border-mid bg-white px-3.5 py-2 text-[13px] font-bold text-body no-underline transition-colors hover:border-primary hover:text-primary"
                    >
                        Редактировать
                    </a>
                </div>
            @endforeach
        @endif
    </div>
</x-layouts.admin>
