<x-layouts.app :title="$metaTitle">
    <section class="bg-gradient-to-b from-surface to-white">
        <x-page-container class="py-14 pb-10">
            <x-breadcrumb :items="$breadcrumb" />
            @if ($hasModelCode)
                <x-eyebrow>{{ $product->code }}</x-eyebrow>
            @endif
            <div class="flex items-start justify-between gap-6 flex-wrap mt-2.5">
                <h1 class="display-heading font-heading font-bold text-[clamp(28px,4.2vw,40px)] text-heading leading-tight max-w-[900px]">
                    {{ $product->name }}
                </h1>
                <x-product-download-button
                    :slug="$product->slug"
                    :code="$product->code"
                    :name="$product->name"
                    :show-model="$hasModelCode"
                    :image-src="$imageSrc"
                    :image-alt="$imageAlt"
                    :description="$product->description"
                    :specs="$product->specs"
                />
            </div>
        </x-page-container>
    </section>

    <x-page-container class="pt-5">
        <div class="grid grid-cols-1 tablet:grid-cols-2 gap-12 items-start">
            <x-product-image
                :alt="$imageAlt"
                :src="$imageSrc"
                aspect-ratio="4/3"
                :tilt="true"
            />
            <div>
                <h2 class="font-heading font-bold text-2xl text-heading">{{ __('ProductDetail.description') }}</h2>
                @foreach ($product->description as $paragraph)
                    <p class="text-base leading-relaxed text-muted mt-3.5">{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
    </x-page-container>

    <div class="relative overflow-hidden">
        @if (empty($product->applications))
            <div aria-hidden="true" class="hero-blueprint-grid pointer-events-none absolute -right-24 top-0 aspect-square w-[min(520px,60vw)] opacity-40"></div>
        @endif
        <x-page-container class="relative pt-12 pb-16">
            <h2 class="font-heading font-bold text-[28px] text-heading mb-5">{{ __('ProductDetail.specsHeading') }}</h2>
            <x-spec-table :specs="$product->specs" />
            @if (!empty($product->applications))
                <h3 class="font-heading font-bold text-[22px] text-heading mt-10">{{ __('ProductDetail.applications') }}</h3>
                <ul class="mt-4 pl-5 text-muted leading-relaxed text-base list-disc">
                    @foreach ($product->applications as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @endif
        </x-page-container>
    </div>

    <x-home-cta-section />

    @if ($related->isNotEmpty())
        <x-page-container class="py-16 pb-20">
            <h2 class="font-heading font-bold text-[28px] text-heading mb-6">{{ __('ProductDetail.relatedTitle') }}</h2>
            <div class="grid grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-3 gap-6 max-mobile:grid-cols-1">
                @foreach ($related as $index => $item)
                    <div data-reveal style="--reveal-offset: 0.75rem; transition-delay: {{ min($index, 8) * 60 }}ms">
                        <x-product-card :product="$item" />
                    </div>
                @endforeach
            </div>
            <div class="mt-8">
                <a href="{{ route('products.index') }}" class="font-bold text-primary">{{ __('ProductDetail.backLink') }}</a>
            </div>
        </x-page-container>
    @endif

    <x-floating-contact-button />
</x-layouts.app>
