@props(['route', 'params' => [], 'variant' => 'primary', 'arrow' => false])
@php
    $variantClasses = match ($variant) {
        'primary' => 'inline-flex items-center justify-center bg-primary text-white font-bold text-[15px] px-7 py-3.5 rounded-sm shadow-btn no-underline transition-[transform,background,color] duration-200 hover:bg-primary-dark hover:text-white hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.97]',
        'secondary' => 'inline-flex items-center justify-center bg-white text-body font-bold text-[15px] px-7 py-3.5 rounded-sm border border-border-mid no-underline transition-[transform,border-color,color] duration-200 hover:border-primary hover:text-primary active:scale-[0.97]',
        'header' => 'inline-flex items-center justify-center bg-primary text-white font-bold text-sm px-5.5 py-3 rounded-sm shadow-none no-underline transition-[transform,background,color] duration-200 hover:bg-primary-dark hover:text-white active:scale-[0.97]',
        'white' => 'inline-flex items-center justify-center bg-white text-primary font-bold px-7 py-3.5 rounded-sm shadow-none no-underline transition-[transform,background,color] duration-200 hover:bg-white hover:text-primary-dark active:scale-[0.97]',
    };
    $class = \App\Support\Tailwind::merge($variantClasses, $arrow ? 'gap-2' : '', $attributes->get('class', ''));
@endphp
<a href="{{ route($route, $params) }}" {{ $attributes->except('class')->merge(['class' => $class]) }}>
    {{ $slot }}
    @if ($arrow)
        <x-icon name="arrow-right" stroke-width="2.25" class="size-4 shrink-0" />
    @endif
</a>
