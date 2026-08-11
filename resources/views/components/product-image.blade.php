@props([
    'alt',
    'src' => null,
    'aspectRatio' => '4/3',
    'sizes' => '(max-width: 900px) 100vw, 33vw',
    'imagePadding' => 'p-4',
    'tilt' => false,
    'autoFrame' => false,
])
@php
    $imageSrc = $src ?? '/assets/pump.png';
    $class = \App\Support\Tailwind::merge('relative bg-white overflow-hidden', $attributes->get('class', ''));
@endphp
<div
    {{ $attributes->except('class')->merge(['class' => $class]) }}
    style="aspect-ratio: {{ $aspectRatio }}; {{ $tilt ? 'transform-style: preserve-3d;' : '' }}"
    @if ($tilt) data-tilt @endif
>
    <div
        aria-hidden="true"
        data-fade-img-skeleton
        class="absolute inset-0 bg-surface transition-opacity duration-300 opacity-100 animate-pulse"
    ></div>
    <img
        src="{{ $imageSrc }}"
        alt="{{ $alt }}"
        loading="lazy"
        sizes="{{ $sizes }}"
        data-fade-img
        @if ($autoFrame) data-auto-frame data-aspect-ratio="{{ $aspectRatio }}" @endif
        class="absolute inset-0 size-full object-contain ease-out transition-[opacity,transform] {{ $autoFrame ? 'scale-[calc(var(--auto-scale,1))] group-hover:scale-[calc(var(--auto-scale,1)*1.05)]' : 'group-hover:scale-105' }} {{ ! $tilt ? 'duration-400' : '' }} {{ $imagePadding }}"
    />
</div>
