{{--
    Port of components/products/ProductDownloadButton.tsx: a visible button
    plus an off-screen (fixed, far off-canvas, 820px fixed width so the PDF
    doesn't depend on viewport width) reproduction of the card, captured by
    html2canvas and turned into a paginated A4 PDF by jsPDF — both loaded
    lazily via dynamic import() (Vite code-splits this into its own chunk).
    See resources/js/product-download.js.
--}}
@props(['slug', 'code', 'name', 'showModel', 'imageSrc', 'imageAlt', 'description', 'specs'])
@php
    $class = \App\Support\Tailwind::merge(
        'inline-flex items-center justify-center gap-2 bg-white text-body font-bold text-[15px] px-5.5 py-3 rounded-sm border border-border-mid no-underline transition-[border-color,color] duration-200 hover:border-primary hover:text-primary disabled:opacity-60 disabled:cursor-not-allowed shrink-0',
        $attributes->get('class', '')
    );
@endphp
<button
    type="button"
    data-product-download
    data-filename="{{ ($showModel ? $code : $slug).'.pdf' }}"
    data-label="{{ __('ProductDetail.download') }}"
    data-downloading-label="{{ __('ProductDetail.downloading') }}"
    {{ $attributes->except('class')->merge(['class' => $class]) }}
>
    <img data-download-icon src="/assets/pdfICON.png" alt="" width="24" height="24" class="size-6 shrink-0 object-contain" aria-hidden="true" />
    <x-icon data-download-spinner name="loader-circle" class="size-4 shrink-0 animate-spin hidden" />
    <span data-download-label>{{ __('ProductDetail.download') }}</span>
</button>

<div aria-hidden="true" style="position: fixed; top: 0; left: -10000px; width: 820px;">
    <div data-product-capture class="bg-white p-10">
        @if ($showModel)
            <div class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ $code }}</div>
        @endif
        <h1 class="font-heading font-bold text-[32px] text-heading leading-tight">{{ $name }}</h1>

        <div class="grid grid-cols-2 gap-12 items-start mt-8">
            <div class="relative bg-white overflow-hidden" style="aspect-ratio: 4/3">
                <img src="{{ $imageSrc }}" alt="{{ $imageAlt }}" class="absolute inset-0 w-full h-full object-contain p-4" />
            </div>
            <div>
                <h2 class="font-heading font-bold text-2xl text-heading">{{ __('ProductDetail.description') }}</h2>
                @foreach ($description as $paragraph)
                    <p class="text-base leading-relaxed text-muted mt-3.5">{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>

        <h2 class="font-heading font-bold text-[28px] text-heading mt-10 mb-5">{{ __('ProductDetail.specsHeading') }}</h2>
        <x-spec-table :specs="$specs" />
    </div>
</div>
