@props(['route' => null, 'params' => []])
@php
    $base = 'bg-white border border-border rounded-xl overflow-hidden transition-[box-shadow,transform] duration-200';
    $linkExtra = $route ? 'group block hover:shadow-card hover:-translate-y-1 active:translate-y-0 active:scale-[0.98]' : '';
    $class = \App\Support\Tailwind::merge($base, $linkExtra, $attributes->get('class', ''));
@endphp
@if ($route)
    <a href="{{ route($route, $params) }}" {{ $attributes->except('class')->merge(['class' => $class]) }}>
        {{ $slot }}
    </a>
@else
    <div {{ $attributes->except('class')->merge(['class' => $class]) }}>
        {{ $slot }}
    </div>
@endif
