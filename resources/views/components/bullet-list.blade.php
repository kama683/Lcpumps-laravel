@props(['items'])
@php
    $class = \App\Support\Tailwind::merge('list-none space-y-2.5 p-0 m-0', $attributes->get('class', ''));
@endphp
<ul {{ $attributes->except('class')->merge(['class' => $class]) }}>
    @foreach ($items as $item)
        <li class="flex gap-2.5 text-sm leading-relaxed text-body">
            <x-icon name="check" stroke-width="2.5" class="mt-0.5 size-4 shrink-0 text-primary" />
            <span>{{ $item }}</span>
        </li>
    @endforeach
</ul>
