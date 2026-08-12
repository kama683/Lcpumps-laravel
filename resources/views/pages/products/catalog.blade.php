@php
    $pageToRoute = [
        '/products' => 'products.index',
        '/products/water' => 'products.water',
        '/products/valves' => 'products.valves',
    ];
@endphp
<x-layouts.app :title="$metaTitle">
    <x-page-container class="pt-12">
        <div class="relative w-full min-h-100 rounded-2xl overflow-hidden flex flex-col justify-center px-6 py-10 tablet:px-12">
            <x-category-hero-image
                :panel-id="$defaultPanelId"
                :images="$heroDataByPanel[$defaultPanelId]['images'] ?? null"
                :fallback-src="$heroDataByPanel[$defaultPanelId]['fallbackSrc']"
                :alt="$heroDataByPanel[$defaultPanelId]['alt']"
            />
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative z-10">
                <x-breadcrumb :items="$breadcrumb" :light="true" />
                <h1 data-catalog-heading class="display-heading font-heading font-bold text-[clamp(32px,4.2vw,44px)] text-white mt-6 leading-tight">
                    {{ $heading }}
                </h1>
                <p class="text-[17px] leading-relaxed text-white/85 mt-4.5 max-w-[880px]">
                    {{ $intro }}
                </p>
            </div>
        </div>
    </x-page-container>

    <x-page-container class="py-10 pb-20">
        <div class="grid grid-cols-1 tablet:grid-cols-[300px_1fr] gap-12 items-start">
            <aside class="tablet:sticky tablet:top-[88px]">
                <div data-catalog-search data-no-results-text="{{ __('CategoryPage.noResults') }}" data-products-base-url="{{ route('products.index') }}">
                    <x-search-field
                        type="search"
                        placeholder="{{ $searchPlaceholder }}"
                        autocomplete="off"
                        role="combobox"
                    />
                    <div data-search-listbox role="listbox" class="hidden mt-2 opacity-100 translate-y-0 transition-[opacity,transform] duration-150"></div>
                </div>

                <div class="mt-4">
                    <button
                        type="button"
                        data-category-menu-toggle
                        aria-expanded="false"
                        class="tablet:hidden flex w-full items-center justify-between gap-3 rounded-lg border border-border-light bg-white px-5 py-3.5 text-left text-sm font-semibold text-heading"
                    >
                        <span data-active-panel-label class="truncate">{{ $defaultPanelLabel }}</span>
                        <x-icon name="chevron-down" data-category-menu-chevron stroke-width="2.25" class="size-4 shrink-0 text-primary transition-transform duration-200" />
                    </button>
                    <div data-category-menu class="grid transition-[grid-template-rows] duration-300 ease-(--ease-entrance) tablet:grid-rows-[1fr] grid-rows-[0fr]">
                        <nav class="overflow-hidden rounded-lg border border-border-light bg-white tablet:overflow-hidden">
                            @foreach (\App\Support\Site::CATALOG_NAV as $nav)
                                @php
                                    $isCurrentPage = $nav['page'] === $currentRoute;
                                    $navLabelKey = $nav['id'] === 'valves' ? 'valvesFooterLabel' : $nav['id'];
                                @endphp
                                @if ($isCurrentPage)
                                    <button
                                        type="button"
                                        data-panel-nav-button
                                        data-panel-id="{{ $nav['id'] }}"
                                        class="block w-full text-left py-4 px-5 text-sm font-semibold border-b border-border-light leading-snug transition-[background,color,box-shadow] duration-150 active:opacity-60 {{ $nav['id'] === $defaultPanelId ? 'bg-surface-alt text-primary shadow-[inset_3px_0_0_#f70620]' : 'text-muted hover:bg-surface hover:text-primary' }}"
                                    >{{ __('CatalogNav.'.$navLabelKey) }}</button>
                                @else
                                    <a
                                        href="{{ route($pageToRoute[$nav['page']]) }}{{ ($nav['category'] === 'pumps' && $nav['page'] === '/products') ? '#'.$nav['id'] : '' }}"
                                        class="block py-4 px-5 text-sm font-semibold text-muted border-b border-border-light leading-snug hover:bg-surface hover:text-primary no-underline last:border-b-0"
                                    >{{ __('CatalogNav.'.$navLabelKey) }}</a>
                                @endif
                            @endforeach
                        </nav>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-surface-alt to-[#f8e4e6] border border-[#f2d8db] rounded-xl p-5.5 mt-6">
                    <x-icon name="message-circle" stroke-width="1.75" class="size-6 text-primary mb-3" />
                    <div class="font-bold text-[15px] text-heading">{{ __('CategoryPage.helpTitle') }}</div>
                    <p class="text-[13px] leading-relaxed text-muted mt-2">{{ __('CategoryPage.helpText') }}</p>
                    <x-arrow-link route="contact.index" class="mt-3.5 text-sm">{{ __('Common.contactUs') }}</x-arrow-link>
                </div>
            </aside>

            <div data-catalog-panels data-panel-ids="{{ json_encode(array_column($panels, 'id')) }}" data-default-panel="{{ $defaultPanelId }}" data-default-title="{{ $title }}">
                @foreach ($panels as $panel)
                    <div
                        data-catalog-panel="{{ $panel['id'] }}"
                        @if ($panel['id'] !== $defaultPanelId) hidden @endif
                        class="transition-opacity duration-150 {{ $panel['id'] === $defaultPanelId ? 'block' : 'hidden' }}"
                    >
                        <h2 class="display-heading font-heading font-bold text-heading text-[clamp(24px,3vw,32px)] leading-tight">
                            {{ $panelLabels[$panel['id']] }}
                        </h2>
                        @if ($panel['id'] !== 'control-panel')
                            @foreach ($panel['section']['paragraphs'] as $paragraph)
                                <p class="text-base leading-relaxed text-muted mt-4">{{ $paragraph }}</p>
                            @endforeach
                        @endif
                        <div class="grid grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-3 gap-6 mt-7 max-mobile:grid-cols-1">
                            @foreach ($panel['section']['products'] as $product)
                                <x-product-card :product="$product" />
                            @endforeach
                            @foreach ($panel['section']['bullets'] ?? [] as $bullet)
                                <div class="bg-white border border-border rounded-xl overflow-hidden">
                                    <x-product-image :alt="'Category item: '.$bullet" />
                                    <div class="px-5 py-4.5">
                                        <h3 class="font-heading font-bold text-[15px] text-body leading-snug">{{ $bullet }}</h3>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-page-container>

    <x-floating-contact-button />

    <script type="application/json" id="catalog-panel-labels">{!! json_encode($panelLabels, JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/json" id="catalog-hero-data">{!! json_encode($heroDataByPanel, JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/json" id="catalog-search-index">{!! json_encode($searchIndex, JSON_UNESCAPED_UNICODE) !!}</script>
</x-layouts.app>
