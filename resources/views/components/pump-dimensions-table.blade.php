@php
    $cell = 'px-2 py-2 text-center whitespace-nowrap';
    $headCell = 'px-2 py-2 text-center whitespace-nowrap font-bold text-[11px] tracking-wide text-nav uppercase';
    $groups = \App\Support\PumpDimensions::groups();
@endphp
<div class="rounded-xl border border-[#e8cfd2] bg-white overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-[12px] text-body min-w-245">
            <thead>
                <tr class="bg-surface-alt border-b border-[#e8cfd2]">
                    <th class="{{ $headCell }} text-left" rowspan="2">{{ __('PumpTable.pumpSeries') }}</th>
                    <th class="{{ $headCell }} text-left" rowspan="2">{{ __('PumpTable.motorModel') }}</th>
                    <th class="{{ $headCell }}" rowspan="2">{{ __('PumpTable.power') }}<br>{{ __('PumpTable.powerUnit') }}</th>
                    <th class="{{ $headCell }}" colspan="{{ count(\App\Support\PumpDimensions::DIMENSION_COLUMNS) }}">{{ __('PumpTable.installDimensions') }}</th>
                    <th class="{{ $headCell }}" rowspan="2">{{ __('PumpTable.mounting') }}<br>n-d</th>
                    <th class="{{ $headCell }}" rowspan="2">{{ __('PumpTable.motorWeight') }}<br>{{ __('PumpTable.motorWeightUnit') }}</th>
                    <th class="{{ $headCell }}" rowspan="2">{{ __('PumpTable.baseWeight') }}<br>{{ __('PumpTable.baseWeightUnit') }}</th>
                </tr>
                <tr class="bg-surface-alt border-b border-[#e8cfd2]">
                    @foreach (\App\Support\PumpDimensions::DIMENSION_COLUMNS as $col)
                        <th class="{{ $headCell }}">{{ $col }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($groups as $groupIndex => $group)
                    @php
                        $alt = $groupIndex % 2 === 1;
                        $spans = \App\Support\PumpDimensions::computeSpans($group['rows']);
                        $altCellClass = $cell.($alt ? ' bg-surface/60' : '');
                    @endphp
                    @foreach ($group['rows'] as $rowIndex => $row)
                        <tr class="border-b border-border-light {{ $alt ? 'bg-surface/60' : '' }} {{ $rowIndex === 0 ? 'border-t border-t-[#e8cfd2]' : '' }}">
                            @if ($rowIndex === 0)
                                <td class="{{ $cell }} text-left font-bold text-heading bg-[#fbeeef] align-middle" rowspan="{{ count($group['rows']) }}">{{ $group['series'] }}</td>
                            @endif
                            <td class="{{ $cell }} text-left font-semibold text-heading">{{ $row['motorModel'] }}</td>
                            <td class="{{ $cell }}">{{ $row['power'] }}</td>
                            @foreach (['B1', 'B2', 'B', 'H', 'h0', 'h', 'h1', 'h2', 'H1'] as $key)
                                @if ($spans[$key][$rowIndex] > 0)
                                    <td class="{{ $altCellClass }}" rowspan="{{ $spans[$key][$rowIndex] }}">{{ $row[$key] }}</td>
                                @endif
                            @endforeach
                            <td class="{{ $cell }}">{{ $row['L'] }}</td>
                            <td class="{{ $cell }}">{{ $row['L0'] }}</td>
                            @foreach (['L3', 'L4', 'P'] as $key)
                                @if ($spans[$key][$rowIndex] > 0)
                                    <td class="{{ $altCellClass }}" rowspan="{{ $spans[$key][$rowIndex] }}">{{ $row[$key] }}</td>
                                @endif
                            @endforeach
                            <td class="{{ $cell }}">{{ $row['L5'] }}</td>
                            @foreach (['t', 'nd'] as $key)
                                @if ($spans[$key][$rowIndex] > 0)
                                    <td class="{{ $altCellClass }}" rowspan="{{ $spans[$key][$rowIndex] }}">{{ $row[$key] }}</td>
                                @endif
                            @endforeach
                            <td class="{{ $cell }}">{{ $row['motorWeight'] }}</td>
                            @if ($spans['baseWeight'][$rowIndex] > 0)
                                <td class="{{ $altCellClass }}" rowspan="{{ $spans['baseWeight'][$rowIndex] }}">{{ $row['baseWeight'] }}</td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
