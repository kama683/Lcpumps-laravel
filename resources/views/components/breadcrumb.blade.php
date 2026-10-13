{{-- $items: array of ['label' => string, 'route' => ?string, 'params' => ?array] --}}
@props(['items', 'light' => false])
<nav aria-label="Breadcrumb" @class(['mb-3.5 flex flex-row items-center text-[13px] leading-none', 'text-white/80' => $light, 'text-subtle' => ! $light])>
    @foreach ($items as $index => $item)
        <span class="inline-flex items-center text-[13px] leading-none">
            @if ($index > 0)
                <x-icon name="chevron-right" class="mx-1.5 block size-3.5 shrink-0 {{ $light ? 'text-white/70' : 'text-subtle' }}" />
            @endif
            @if (! empty($item['route']))
                <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="text-[13px] leading-none {{ $light ? 'text-white/80 hover:text-white' : 'text-subtle hover:text-primary' }}">
                    {{ $item['label'] }}
                </a>
            @else
                <span class="text-[13px] leading-none">{{ $item['label'] }}</span>
            @endif
        </span>
    @endforeach
</nav>
