{{-- Ported from components/admin/StatCard.tsx (icon dropped — not worth a new icon dependency for Stage 8's scope). --}}
@props(['label', 'value'])

<div class="rounded-2xl border border-border bg-white p-5">
    <div class="text-xs font-semibold uppercase tracking-wide text-subtle">{{ $label }}</div>
    <div class="mt-2 font-heading text-2xl font-bold text-heading">{{ $value }}</div>
</div>
