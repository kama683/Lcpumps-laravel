@props(['eyebrow' => null, 'title', 'description' => null, 'center' => false])
<div @class(['text-center' => $center])>
    @if ($eyebrow)
        <x-eyebrow @class(['mb-2.5' => $center])>{{ $eyebrow }}</x-eyebrow>
    @endif
    <h2 @class(['font-heading font-bold text-heading text-[clamp(26px,3vw,38px)]', 'mt-2.5' => $eyebrow])>
        {{ $title }}
    </h2>
    @if ($description)
        <p @class(['text-[17px] text-muted mt-3.5 leading-relaxed max-w-[680px]', 'mx-auto' => $center])>
            {{ $description }}
        </p>
    @endif
</div>
