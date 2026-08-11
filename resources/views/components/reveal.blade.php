{{--
    Progressive-enhancement scroll reveal — see [data-reveal] rules in
    resources/css/app.css and resources/js/reveal.js. Visible by default;
    only animates once JS confirms it can observe intersections.
--}}
@props(['delay' => 0])
<div data-reveal {{ $attributes }} @if ($delay) style="transition-delay: {{ $delay }}ms" @endif>
    {{ $slot }}
</div>
