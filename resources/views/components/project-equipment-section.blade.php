{{--
    Ports components/projects/ProjectEquipmentSection.tsx. $products: array of
    stdClass from CatalogService::productsBySlugs(). RevealStagger's per-item
    fade is reproduced with x-reveal + a computed delay (60ms/item, capped at
    8 items), same constants as the source's STAGGER_STEP_MS/STAGGER_CAP.
--}}
@props(['description', 'products'])
@if (count($products))
    <section class="mt-14">
        <x-eyebrow>{{ __('ProjectDetail.equipmentEyebrow') }}</x-eyebrow>
        <h2 class="font-heading font-bold text-[clamp(24px,3vw,32px)] text-heading mt-2.5">
            {{ __('ProjectDetail.equipmentTitle') }}
        </h2>
        <p class="text-[15px] leading-relaxed text-muted mt-3.5 max-w-[720px]">
            {{ $description }}
        </p>
        <div class="grid grid-cols-2 tablet:grid-cols-4 gap-4 mt-7 max-mobile:grid-cols-1">
            @foreach ($products as $product)
                <x-reveal :delay="min($loop->index, 8) * 60">
                    <x-project-equipment-card :product="$product" />
                </x-reveal>
            @endforeach
        </div>
        <p class="mt-6 text-sm">
            <x-arrow-link route="products.index">{{ __('ProjectDetail.viewCatalog') }}</x-arrow-link>
        </p>
    </section>
@endif
