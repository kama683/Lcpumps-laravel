{{-- $items: array of ['label' => string, 'route' => ?string, 'params' => ?array] --}}
@props(['items', 'light' => false])
<nav aria-label="Breadcrumb" @class(['text-[13px] mb-3.5', 'text-white/80' => $light, 'text-subtle' => ! $light])>
    @foreach ($items as $index => $item)
        <span class="inline-flex items-center">
            @if ($index > 0)
                <x-icon name="chevron-right" class="mx-1.5 inline size-3.5 align-middle {{ $light ? 'text-white/70' : 'text-subtle' }}" />
            @endif
            @if (! empty($item['route']))
                <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="{{ $light ? 'text-white/80 hover:text-white' : 'text-subtle hover:text-primary' }}">
                    {{ $item['label'] }}
                </a>
            @else
                <span>{{ $item['label'] }}</span>
            @endif
        </span>
    @endforeach
</nav>
