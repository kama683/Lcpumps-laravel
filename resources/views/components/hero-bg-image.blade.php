{{--
    Ports components/ui/HeroBgImage.tsx. Only "page" and "contact" are used
    anywhere in the source — "catalog" and "home" (PumpHeroBackdrop's other
    layout) are dead code, never invoked by any page (home builds its own
    hero inline, not through this component).
--}}
@props(['variant' => 'page'])
@if ($variant === 'contact')
    @php
        $class = \App\Support\Tailwind::merge(
            'absolute right-63 top-1/2 -translate-y-1/2 w-[min(520px,42vw)] max-tablet:w-[min(400px,78vw)] z-0 pointer-events-none flex items-center justify-center',
            $attributes->get('class', '')
        );
    @endphp
    <div {{ $attributes->except('class')->merge(['class' => $class]) }} aria-hidden="true">
        <div class="hero-blueprint-grid absolute left-[60%] top-[40%] -translate-x-1/2 -translate-y-1/2 aspect-square w-[min(780px,70vw)] max-tablet:w-[min(560px,95vw)]"></div>
        <img
            src="/assets/pump without background.png"
            alt=""
            width="1666"
            height="944"
            class="relative z-1 w-full h-auto object-contain"
        >
    </div>
@else
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
@endif
