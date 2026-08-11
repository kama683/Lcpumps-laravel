@props(['photoSrc', 'photoAlt', 'videoSrc' => '/assets/VIDEO.mp4'])
@php $extra = $attributes->get('class'); @endphp
<button
    type="button"
    data-about-video
    data-video-src="{{ $videoSrc }}"
    data-extra-class="{{ $extra }}"
    aria-label="Воспроизвести видео"
    {{ $attributes->class('group/play absolute inset-0 size-full cursor-pointer') }}
>
    <img src="{{ $photoSrc }}" alt="{{ $photoAlt }}" loading="lazy" class="absolute inset-0 size-full object-cover" />
    <span class="absolute inset-0 bg-black/15 transition-colors duration-300 group-hover/play:bg-black/25"></span>
    <span class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 flex items-center justify-center size-16 rounded-full bg-white/95 shadow-lg transition-transform duration-300 group-hover/play:scale-110">
        <x-icon name="play" fill="currentColor" :stroke-width="0" class="size-6 text-primary ml-1" />
    </span>
</button>
