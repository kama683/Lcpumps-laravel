@props(['src' => null, 'alt', 'aspectRatio' => '16/10'])
@php
    $class = \App\Support\Tailwind::merge('relative bg-surface overflow-hidden', $attributes->get('class', ''));
@endphp
<div {{ $attributes->except('class')->merge(['class' => $class]) }} style="aspect-ratio: {{ $aspectRatio }}">
    @if ($src)
        <div
            aria-hidden="true"
            data-fade-img-skeleton
            class="absolute inset-0 bg-surface transition-opacity duration-300 opacity-100 animate-pulse"
        ></div>
        <img
            src="{{ $src }}"
            alt="{{ $alt }}"
            loading="lazy"
            sizes="(max-width: 900px) 100vw, 50vw"
            data-fade-img
            class="absolute inset-0 size-full object-cover transition-[opacity,transform] duration-400 ease-out group-hover:scale-105"
        />
    @else
        <img
            src="/assets/pump.png"
            alt="{{ $alt }}"
            sizes="(max-width: 900px) 100vw, 50vw"
            class="absolute inset-0 h-full w-full object-contain p-3"
        />
    @endif
</div>
