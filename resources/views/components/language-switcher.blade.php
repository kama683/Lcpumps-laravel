{{--
    Dropdown mode uses native <details>/<summary> so open/close works with
    zero JS (no client router state like the source's LanguageDropdown);
    resources/js/language-switcher.js only adds outside-click/Escape closing
    as a polish layer on top of that native behavior.
--}}
@props(['inline' => false, 'light' => false])
@php
    $locales = ['ru', 'kk', 'en', 'zh'];
    $current = app()->getLocale();
@endphp
@if ($inline)
    <div class="flex flex-wrap items-center gap-1.5 text-sm font-semibold">
        @foreach ($locales as $loc)
            <a
                href="{{ locale_url($loc) }}"
                @if ($loc === $current) aria-current="true" @endif
                class="rounded-md px-3 py-2 transition-colors active:opacity-60 cursor-pointer {{ $loc === $current ? 'bg-pill-blue-bg text-primary' : 'text-nav hover:bg-surface hover:text-primary' }}"
            >{{ __('LanguageSwitcher.'.$loc) }}</a>
        @endforeach
    </div>
@else
    <details data-language-dropdown class="group relative text-sm font-semibold">
        <summary
            class="list-none [&::-webkit-details-marker]:hidden flex items-center gap-1.5 rounded-sm px-2 py-2.5 tablet:py-1 transition-colors duration-300 cursor-pointer active:opacity-60 {{ $light ? 'text-white hover:text-white/80' : 'text-nav hover:text-primary' }}"
        >
            {{ __('LanguageSwitcher.'.$current) }}
            <x-icon name="chevron-down" stroke-width="2.25" class="size-3.5 shrink-0 transition-transform duration-200 group-open:rotate-180" />
        </summary>
        <div
            role="listbox"
            class="absolute right-0 top-full z-10 mt-1.5 min-w-23 origin-top-right overflow-hidden rounded-md border border-border-mid bg-white py-1 shadow-card"
        >
            @foreach ($locales as $loc)
                <a
                    href="{{ locale_url($loc) }}"
                    role="option"
                    @if ($loc === $current) aria-selected="true" @endif
                    class="block w-full cursor-pointer px-3.5 py-2.5 text-left transition-colors active:opacity-60 {{ $loc === $current ? 'bg-pill-blue-bg text-primary' : 'text-nav hover:bg-surface hover:text-primary' }}"
                >{{ __('LanguageSwitcher.'.$loc) }}</a>
            @endforeach
        </div>
    </details>
@endif
