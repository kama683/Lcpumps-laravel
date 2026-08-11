@props(['wrapperClass' => null])
<div @class(['relative', $wrapperClass])>
    <x-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 size-[18px] -translate-y-1/2 text-subtle" />
    <input
        type="search"
        {{ $attributes->class('w-full rounded-md border border-border-mid bg-white py-3 pl-10 pr-4 text-sm text-body focus:border-primary focus:shadow-[0_0_0_3px_rgba(247,6,32,0.12)] focus:outline-none') }}
    />
</div>
