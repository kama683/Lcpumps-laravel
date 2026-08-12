@props(['icon', 'active' => false, 'size' => 'sm'])
@php
    $sizeClasses = [
        'sm' => 'w-11 h-11 rounded-xl [&_svg]:size-[22px]',
        'md' => 'w-14 h-14 rounded-[14px] [&_svg]:size-7',
        'lg' => 'w-16 h-16 rounded-2xl [&_svg]:size-8',
    ];
    $class = \App\Support\Tailwind::merge(
        'flex items-center justify-center shrink-0 bg-surface-alt text-primary transition-colors duration-200',
        $sizeClasses[$size] ?? $sizeClasses['sm'],
        $active ? 'bg-primary text-white' : '',
        $attributes->get('class', '')
    );
@endphp
<div {{ $attributes->except('class')->merge(['class' => $class]) }}>
    <x-icon :name="$icon" stroke-width="2" />
</div>
