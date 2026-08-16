@php
    $pageToRoute = [
        '/products' => 'products.index',
        '/products/water' => 'products.water',
        '/products/valves' => 'products.valves',
    ];
@endphp
<footer class="bg-footer text-footer-text">
    <div class="max-w-[var(--container-content)] mx-auto px-5 tablet:px-8 pt-12 tablet:pt-16 pb-10">
        <div class="grid grid-cols-1 tablet:grid-cols-5 gap-10">
            <div class="tablet:col-span-1">
                <div class="flex items-center gap-3">
                    <img
                        src="{{ \App\Support\Site::LOGO_PATH }}"
                        alt="{{ \App\Support\Site::BRAND }}"
                        width="44"
                        height="44"
                        loading="lazy"
                        class="object-contain bg-white rounded-sm p-1"
                    />
                    <span class="font-heading font-bold text-lg text-white">{{ \App\Support\Site::BRAND }}</span>
                </div>
                <p class="text-sm leading-relaxed text-footer-muted mt-4 max-w-xs">{{ \App\Support\Site::COMPANY_NAME }}</p>
            </div>

            <div>
                <h4 class="text-[13px] font-bold tracking-wide uppercase text-white mb-4">{{ __('Footer.navigationHeading') }}</h4>
                <div class="flex flex-col gap-2.5 text-sm">
                    <a href="{{ route('home') }}" class="text-footer-link hover:text-primary no-underline">{{ __('Nav.home') }}</a>
                    <a href="{{ route('about-us.index') }}" class="text-footer-link hover:text-primary no-underline">{{ __('Nav.aboutUs') }}</a>
                    <a href="{{ route('products.index') }}" class="text-footer-link hover:text-primary no-underline">{{ __('Nav.catalog') }}</a>
                    <a href="{{ route('projects.index') }}" class="text-footer-link hover:text-primary no-underline">{{ __('Nav.projects') }}</a>
                    <a href="{{ route('about-us.show', ['slug' => 'certifications']) }}" class="text-footer-link hover:text-primary no-underline">{{ __('Footer.certifications') }}</a>
                </div>
            </div>

            <div class="tablet:col-span-2">
                <h4 class="text-[13px] font-bold tracking-wide uppercase text-white mb-4">{{ __('Footer.productsHeading') }}</h4>
                <div class="grid grid-cols-2 max-mobile:grid-cols-1 gap-x-6 gap-y-2.5 text-sm">
                    @foreach (\App\Support\Site::CATALOG_NAV as $item)
                        @php
                            $route = $pageToRoute[$item['page']];
                            $fragment = ($item['category'] === 'pumps' && $item['page'] === '/products') ? '#'.$item['id'] : '';
                            $labelKey = $item['id'] === 'valves' ? 'valvesFooterLabel' : $item['id'];
                        @endphp
                        <a href="{{ route($route).$fragment }}" class="text-footer-link hover:text-primary no-underline">{{ __('CatalogNav.'.$labelKey) }}</a>
                    @endforeach
                </div>
            </div>

            <div>
                <h4 class="text-[13px] font-bold tracking-wide uppercase text-white mb-4">{{ __('Footer.contactsHeading') }}</h4>
                <div class="flex flex-col gap-3 text-sm text-footer-link leading-relaxed">
                    <span>{{ \App\Support\Site::COMPANY_NAME }}</span>
                    <span class="inline-flex items-start gap-2">
                        <x-icon name="map-pin" class="mt-0.5 size-4 shrink-0 text-primary" />
                        {{ __('Footer.address') }}
                    </span>
                    <a href="mailto:info@lcpumps.kz" class="inline-flex items-start gap-2 text-footer-link hover:text-primary no-underline">
                        <x-icon name="mail" class="mt-0.5 size-4 shrink-0 text-primary" />
                        info@lcpumps.kz
                    </a>
                    <a href="tel:+77717786877" class="inline-flex items-start gap-2 text-footer-link hover:text-primary no-underline">
                        <x-icon name="phone" class="mt-0.5 size-4 shrink-0 text-primary" />
                        +7 771 778 68 77
                    </a>
                </div>
            </div>
        </div>

        <div class="border-t border-[#33434d] mt-11 pt-5 text-[13px] text-footer-muted">
            <span>&copy; {{ \App\Support\Site::COMPANY_NAME }} {{ __('Footer.rightsReserved') }}</span>
        </div>
    </div>
</footer>
