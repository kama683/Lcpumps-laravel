@props(['narrow' => false])
@php
    $class = \App\Support\Tailwind::merge(
        'mx-auto px-5 tablet:px-8',
        $narrow ? 'max-w-[800px]' : 'max-w-[var(--container-content)]',
        $attributes->get('class', '')
    );
@endphp
<div {{ $attributes->except('class')->merge(['class' => $class]) }}>
    {{ $slot }}
</div>
