{{-- Same data-accordion-* contract as x-accordion-item (resources/js/accordion.js is shared), different chrome. --}}
@props(['title'])
@php
    $class = \App\Support\Tailwind::merge('group bg-white border border-border-mid rounded-lg overflow-hidden mt-5', $attributes->get('class', ''));
@endphp
<details data-accordion-item {{ $attributes->except('class')->merge(['class' => $class]) }}>
    <summary data-accordion-summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5.5 py-4.5 font-bold text-[15px] text-body transition-opacity active:opacity-60 [&::-webkit-details-marker]:hidden">
        <span>{{ $title }}</span>
        <x-icon name="chevron-down" stroke-width="2.25" class="size-5 shrink-0 text-primary transition-transform duration-200 group-open:rotate-180" />
    </summary>
    <div data-accordion-panel class="grid transition-[grid-template-rows] duration-500 ease-(--ease-entrance)">
        <div class="overflow-hidden">
            <div class="px-5.5 pb-5.5">{{ $slot }}</div>
        </div>
    </div>
</details>
