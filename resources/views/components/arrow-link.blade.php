@props(['route' => null, 'params' => [], 'href' => null, 'hash' => null])
@php
    $class = \App\Support\Tailwind::merge('inline-flex items-center gap-1.5 font-bold text-primary no-underline transition-[gap,color] duration-200 hover:gap-2.5 hover:text-primary-dark', $attributes->get('class', ''));
    $url = $route ? route($route, $params) : $href;
    if ($hash) {
        $url .= '#'.$hash;
    }
@endphp
<a href="{{ $url }}" {{ $attributes->except('class')->merge(['class' => $class]) }}>
    {{ $slot }}
    <x-icon name="arrow-right" stroke-width="2.25" class="size-4 shrink-0" />
</a>
