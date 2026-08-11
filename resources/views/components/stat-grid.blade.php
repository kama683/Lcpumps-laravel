{{-- $items: array of ['value' => string, 'label' => string] --}}
@props(['items', 'columns' => 4, 'compact' => false])
@php
    $gridCols = $compact || $columns === 5
        ? 'grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-5 max-mobile:grid-cols-1'
        : 'grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-4 max-mobile:grid-cols-1';
@endphp
<div class="grid gap-px bg-stat-grid border border-stat-grid rounded-2xl overflow-hidden {{ $gridCols }}">
    @foreach ($items as $index => $item)
        <div
            data-reveal
            style="--reveal-offset: 0.75rem; transition-delay: {{ min($index, 8) * 60 }}ms"
            class="bg-white {{ $compact ? 'px-[18px] py-5.5 text-center' : 'px-6 py-7' }}"
        >
            <div
                data-count-up
                data-value="{{ $item['value'] }}"
                class="tabular-nums {{ $compact ? 'font-heading font-bold text-primary leading-none text-[28px]' : 'font-heading font-bold text-primary leading-none text-4xl' }}"
            >{{ $item['value'] }}</div>
            <div class="text-muted mt-2.5 leading-snug {{ $compact ? 'text-[13px] font-semibold text-muted-light' : 'text-sm' }}">
                {{ $item['label'] }}
            </div>
        </div>
    @endforeach
</div>
