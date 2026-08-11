@php
    $currentRouteName = preg_replace('/^loc\./', '', (string) request()->route()?->getName());
    $isHome = $currentRouteName === 'home';
    // isActiveNav port: exact match for "/", prefix match otherwise.
    $navItems = [
        ['route' => 'home', 'labelKey' => 'home', 'active' => $isHome],
        ['route' => 'about-us.index', 'labelKey' => 'aboutUs', 'active' => str_starts_with($currentRouteName, 'about-us')],
        ['route' => 'products.index', 'labelKey' => 'catalog', 'active' => str_starts_with($currentRouteName, 'products')],
        ['route' => 'projects.index', 'labelKey' => 'projects', 'active' => str_starts_with($currentRouteName, 'projects')],
        ['route' => 'contact.index', 'labelKey' => 'contacts', 'active' => str_starts_with($currentRouteName, 'contact')],
    ];
@endphp
{{--
    Home hero is a fullscreen photo, so the header floats transparent over it
    until the user scrolls past it or hovers — every other page keeps the
    regular solid header. Server renders the correct first-paint state
    ($isHome means transparent, matching scrolled=false/hovered=false on
    mount); resources/js/header.js swaps the class lists below (kept there
    verbatim so Tailwind's scanner still finds them) on scroll/hover/menu
    toggle.
--}}
<header
    data-header
    data-is-home="{{ $isHome ? '1' : '0' }}"
    class="top-0 inset-x-0 z-50 border-b transition-colors duration-300 {{ $isHome ? 'fixed' : 'sticky' }} {{ $isHome ? 'bg-black/20 border-transparent' : 'header-material bg-white/96 backdrop-blur-sm border-border-light' }}"
>
    <div class="max-w-[var(--container-content)] mx-auto px-5 tablet:px-8 py-3 tablet:py-3.5 flex items-center gap-4 tablet:gap-8.5">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 tablet:gap-3.5 shrink-0 no-underline hover:text-inherit">
            <img
                src="{{ \App\Support\Site::LOGO_PATH }}"
                alt="{{ \App\Support\Site::BRAND }}"
                width="88"
                height="88"
                fetchpriority="high"
                class="object-contain size-20 tablet:h-28 tablet:w-auto"
            />
            <span data-header-brand class="font-heading font-bold text-[28px] tablet:text-[32px] tracking-[-0.3px] leading-[1.15] transition-colors duration-300 {{ $isHome ? 'text-white' : 'text-body' }}">
                LCPUMPS
            </span>
        </a>

        <nav class="hidden tablet:flex ml-auto items-center gap-7 text-[15px] font-semibold whitespace-nowrap">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    data-header-nav-link
                    data-active="{{ $item['active'] ? '1' : '0' }}"
                    class="no-underline pb-0.5 transition-colors duration-300 {{ $isHome ? ($item['active'] ? 'text-white border-b-2 border-white' : 'text-white/85 hover:text-white') : ($item['active'] ? 'text-primary border-b-2 border-primary' : 'text-nav hover:text-primary') }}"
                >{{ __('Nav.'.$item['labelKey']) }}</a>
            @endforeach
        </nav>

        <div class="hidden tablet:block">
            <x-language-switcher :light="$isHome" />
        </div>

        <x-button route="contact.index" variant="header" class="hidden tablet:inline-flex shrink-0">
            {{ __('Nav.contactCta') }}
        </x-button>

        <button
            type="button"
            data-header-menu-toggle
            data-open-label="{{ __('Nav.openMenu') }}"
            data-close-label="{{ __('Nav.closeMenu') }}"
            aria-expanded="false"
            aria-label="{{ __('Nav.openMenu') }}"
            class="tablet:hidden ml-auto flex size-11 shrink-0 items-center justify-center rounded-md border transition-colors duration-300 {{ $isHome ? 'border-white/40 text-white hover:border-white hover:text-white' : 'border-border-mid text-heading hover:border-primary hover:text-primary' }}"
        >
            <span class="relative block size-5">
                <x-icon name="menu" data-menu-icon class="absolute inset-0 transition-[opacity,transform] duration-200 ease-(--ease-entrance) scale-100 rotate-0 opacity-100" />
                <x-icon name="x" data-close-icon class="absolute inset-0 transition-[opacity,transform] duration-200 ease-(--ease-entrance) scale-75 -rotate-45 opacity-0" />
            </span>
        </button>
    </div>

    <div data-header-mobile-panel class="tablet:hidden grid overflow-hidden border-t border-border-light bg-white transition-[grid-template-rows] duration-300 ease-(--ease-entrance) grid-rows-[0fr]">
        <div class="overflow-hidden">
            <nav class="flex flex-col gap-1 px-5 py-4">
                @foreach ($navItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="rounded-md px-4 py-3.5 text-base font-semibold no-underline transition-colors {{ $item['active'] ? 'bg-surface-alt text-primary' : 'text-nav hover:bg-surface hover:text-primary' }}"
                    >{{ __('Nav.'.$item['labelKey']) }}</a>
                @endforeach
            </nav>
            <div class="flex flex-col gap-3.5 border-t border-border-light px-5 py-4">
                <x-language-switcher inline />
                <x-button route="contact.index" variant="header" class="justify-center">
                    {{ __('Nav.contactCta') }}
                </x-button>
            </div>
        </div>
    </div>
</header>
