@props(['narrow' => false])
<div {{ $attributes->class(['mx-auto px-5 tablet:px-8', 'max-w-[800px]' => $narrow, 'max-w-[var(--container-content)]' => ! $narrow]) }}>
    {{ $slot }}
</div>
