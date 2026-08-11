{{--
    $images: array of src strings for this panel's slideshow, or null/empty
    to fall back to a single static photo. Keyed by panel-id via wire:key-
    style DOM replacement isn't available server-side, so the crossfade
    restart-on-category-switch (the source's React `key={panelId}`) is
    handled by resources/js/catalog-panel.js re-initializing this element
    whenever the visible panel changes.
--}}
@props(['panelId', 'images' => null, 'fallbackSrc', 'alt'])
<div
    data-category-hero
    data-panel-id="{{ $panelId }}"
    class="absolute inset-0 opacity-100 transition-opacity duration-350 ease-(--ease-entrance)"
>
    @if (empty($images))
        <img
            src="{{ $fallbackSrc }}"
            alt="{{ $alt }}"
            fetchpriority="high"
            class="absolute inset-0 size-full object-center object-cover"
        />
    @else
        @foreach ($images as $index => $src)
            <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                @if ($index === 0) fetchpriority="high" @else loading="eager" @endif
                data-slide
                class="absolute inset-0 size-full object-center object-cover transition-opacity ease-in-out"
                style="opacity: {{ $index === 0 ? 1 : 0 }}; transition-duration: 700ms"
            />
        @endforeach
    @endif
</div>
