@props(['title'])
<details data-accordion-item class="group bg-white border border-border rounded-lg px-6 py-5 mb-3.5">
    <summary data-accordion-summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-bold text-[17px] text-body transition-opacity active:opacity-60 [&::-webkit-details-marker]:hidden">
        <span>{{ $title }}</span>
        <x-icon name="chevron-down" stroke-width="2.25" class="size-5 shrink-0 text-primary transition-transform duration-200 group-open:rotate-180" />
    </summary>
    <div data-accordion-panel class="grid transition-[grid-template-rows] duration-500 ease-(--ease-entrance)">
        <div class="overflow-hidden">
            <div class="text-[15px] leading-relaxed text-muted mt-3.5">{{ $slot }}</div>
        </div>
    </div>
</details>
