{{--
    Ports components/ui/HeroBgImage.tsx. Only the "page" variant is used by
    any Stage 5 batch-3/4 page (services/projects/faq) — the source's
    "catalog" variant is dead code (never invoked anywhere), and "contact"
    (PumpHeroBackdrop) is Stage 5 batch 5 scope.
--}}
@props(['variant' => 'page'])
@php
    $class = \App\Support\Tailwind::merge('absolute -right-[-180px] top-1/2 -translate-y-1/2 w-[min(360px,40vw)] h-auto object-contain opacity-[0.34] z-0 pointer-events-none max-tablet:-right-[120px] max-tablet:opacity-[0.22] max-tablet:w-[min(420px,60vw)]', $attributes->get('class', ''));
@endphp
<img
    src="{{ \App\Support\Site::LOGO_PATH }}"
    alt=""
    aria-hidden="true"
    width="1024"
    height="1024"
    {{ $attributes->except('class')->merge(['class' => $class]) }}
>
