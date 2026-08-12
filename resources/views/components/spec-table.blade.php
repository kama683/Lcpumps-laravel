{{-- $specs: array of strings, each "key: value" or a bare label (see App\Support\Specs::parseRow) --}}
@props(['specs'])
@if (!empty($specs))
    @php
        $class = \App\Support\Tailwind::merge('w-full min-w-115 border-collapse text-[15px]', $attributes->get('class', ''));
    @endphp
    <div class="overflow-x-auto">
        <table class="{{ $class }}">
            <tbody>
                @foreach ($specs as $spec)
                    @php [$key, $value] = \App\Support\Specs::parseRow($spec); @endphp
                    <tr class="border-b border-border-light">
                        <th class="w-[38%] px-[18px] py-3.5 text-left text-muted font-semibold bg-surface">{{ $key }}</th>
                        <td class="px-[18px] py-3.5 text-body font-semibold">{{ $value }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
