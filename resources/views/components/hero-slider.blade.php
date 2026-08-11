{{--
    $images: array of ['src' => string, 'alt' => string]. Crossfading
    fullscreen background slideshow with scroll-linked parallax — see
    resources/js/hero-slider.js. First slide renders eagerly/visibly;
    without JS this is simply a static first slide (no crossfade), which is
    an acceptable no-JS fallback for a purely decorative background.
--}}
@props(['images'])
@php
    $class = \App\Support\Tailwind::merge('absolute inset-0 z-0 overflow-hidden', $attributes->get('class', ''));
@endphp
<div {{ $attributes->except('class')->merge(['class' => $class]) }} aria-hidden="true" data-hero-slider>
    @foreach ($images as $index => $image)
        <img
            src="{{ $image['src'] }}"
            alt="{{ $image['alt'] }}"
            @if ($index === 0) fetchpriority="high" @else loading="eager" @endif
            data-slide
            class="absolute inset-0 size-full object-cover object-center transition-opacity ease-in-out"
            style="opacity: {{ $index === 0 ? 1 : 0 }}; transition-duration: 900ms"
        />
    @endforeach
    <div class="absolute inset-0 bg-black/40"></div>
</div>
