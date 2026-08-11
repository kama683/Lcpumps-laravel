@php
    $class = \App\Support\Tailwind::merge('text-[13px] font-bold tracking-[1.5px] uppercase text-primary', $attributes->get('class', ''));
@endphp
<div {{ $attributes->except('class')->merge(['class' => $class]) }}>
    {{ $slot }}
</div>
