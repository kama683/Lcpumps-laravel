{{--
    Ported from components/admin/ProductEditModal.tsx. Full page instead of
    a client-side modal (no SPA layer here); the per-locale tab UI becomes
    stacked sections (RU/EN/KK/ZH headings) — still no JS required to reach
    every field, safer than hiding fields behind tabs a validation error
    might land on. specs/description/applications are one-line-per-item
    textareas rather than dynamic add/remove rows — same stored format
    (array of strings), simpler to build and to use.
--}}
@php
    $localeLabels = ['ru' => 'RU', 'en' => 'EN', 'kk' => 'KK', 'zh' => 'ZH'];
    $old = fn (string $key, $default = '') => old($key, $default);
@endphp
<x-layouts.admin :title="$mode === 'create' ? 'LCPumps Admin — Новый товар' : 'LCPumps Admin — '.$product['code']">
    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary no-underline">
        &larr; Назад к товарам
    </a>

    <h1 class="mt-4 font-heading text-[26px] font-bold text-heading">
        {{ $mode === 'create' ? 'Новый товар' : 'Редактировать: '.$product['code'] }}
    </h1>

    @if ($errors->any())
        <div class="mt-4 rounded-md border border-[#f3c9c9] bg-[#fdf1f1] px-4 py-3 text-sm font-semibold text-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ $mode === 'create' ? route('admin.products.store') : route('admin.products.update', ['slug' => $product['slug']]) }}"
        class="mt-6 flex flex-col gap-5"
    >
        @csrf
        @if ($mode === 'edit')
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 gap-3 mobile:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Slug</label>
                <input
                    id="slug-field"
                    class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body disabled:bg-surface disabled:text-subtle"
                    name="slug"
                    value="{{ $mode === 'create' ? $old('slug') : $product['slug'] }}"
                    placeholder="my-pump-model"
                    @if ($mode === 'edit') disabled @endif
                >
                @if ($mode === 'edit')
                    <input type="hidden" name="slug" value="{{ $product['slug'] }}">
                @endif
                @if ($mode === 'create')
                    <p class="mt-1 text-xs text-subtle">Латиница, цифры, дефис. Нельзя изменить после создания.</p>
                @endif
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Код модели</label>
                <input
                    class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body"
                    name="code"
                    value="{{ $old('code', $product['code'] ?? '') }}"
                >
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 mobile:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Категория</label>
                <select class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body" name="category">
                    @foreach ($categoryLabels as $value => $label)
                        <option value="{{ $value }}" @selected($old('category', $product['category'] ?? 'pumps') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Секция каталога</label>
                <select class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body" name="section_id">
                    <option value="">— не выбрано —</option>
                    @foreach ($sections as $section)
                        <option value="{{ $section->id }}" @selected($old('section_id', $product['section_id'] ?? '') === $section->id)>{{ $section->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 mobile:grid-cols-2">
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Количество в наличии</label>
                <input
                    type="number" min="0" step="1"
                    class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body"
                    name="stock_quantity"
                    value="{{ $old('stock_quantity', $product['stock_quantity'] ?? 0) }}"
                >
                <p class="mt-1 text-xs text-subtle">Видно и редактируется только в админ-панели.</p>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Цена</label>
                <input
                    type="number" min="0" step="1"
                    class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body"
                    name="price"
                    value="{{ $old('price', $product['price'] ?? 0) }}"
                >
                <p class="mt-1 text-xs text-subtle">Видно и редактируется только в админ-панели.</p>
            </div>
        </div>

        <div>
            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Изображение</label>
            @include('admin.products._image-upload-field', ['slug' => $mode === 'edit' ? $product['slug'] : null, 'image' => $old('image', $product['image'] ?? '')])
        </div>

        <div class="flex flex-col gap-6 border-t border-border-light pt-4">
            @foreach ($locales as $locale)
                @php $t = $product['translations'][$locale] ?? ['name' => '', 'description' => [], 'specs' => [], 'applications' => []]; @endphp
                <div>
                    <h2 class="mb-3 text-xs font-bold uppercase tracking-wide text-primary">{{ $localeLabels[$locale] }}</h2>

                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Название</label>
                            <input
                                class="w-full rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body"
                                name="translations[{{ $locale }}][name]"
                                value="{{ $old("translations.{$locale}.name", $t['name']) }}"
                            >
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Описание (каждый абзац с новой строки)</label>
                            <textarea
                                class="min-h-30 w-full resize-y rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body"
                                name="translations[{{ $locale }}][description]"
                            >{{ $old("translations.{$locale}.description", implode("\n", $t['description'])) }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Характеристики (каждая «параметр: значение» с новой строки)</label>
                            <textarea
                                class="min-h-24 w-full resize-y rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body"
                                name="translations[{{ $locale }}][specs]"
                            >{{ $old("translations.{$locale}.specs", implode("\n", $t['specs'])) }}</textarea>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-subtle">Области применения (каждая с новой строки)</label>
                            <textarea
                                class="min-h-24 w-full resize-y rounded-md border border-border-mid bg-white px-3.5 py-2.5 text-sm text-body"
                                name="translations[{{ $locale }}][applications]"
                            >{{ $old("translations.{$locale}.applications", implode("\n", $t['applications'])) }}</textarea>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-border-light pt-5">
            @if ($mode === 'edit')
                <button
                    type="submit"
                    form="delete-product-form"
                    onclick="return confirm('Удалить товар «{{ $product['code'] }}»? Это необратимо.')"
                    class="inline-flex items-center gap-1.5 border-none bg-transparent text-sm font-semibold text-muted hover:text-error"
                >
                    Удалить товар
                </button>
            @else
                <span></span>
            @endif
            <button type="submit" class="rounded-sm bg-primary px-6 py-3 text-sm font-bold text-white shadow-btn transition-[background,transform] duration-200 hover:-translate-y-0.5 hover:bg-primary-dark">
                Сохранить
            </button>
        </div>
    </form>

    @if ($mode === 'edit')
        <form id="delete-product-form" method="POST" action="{{ route('admin.products.destroy', ['slug' => $product['slug']]) }}">
            @csrf
            @method('DELETE')
        </form>
    @endif
</x-layouts.admin>
