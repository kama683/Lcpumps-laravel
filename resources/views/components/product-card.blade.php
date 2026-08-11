{{--
    $product: stdClass (from CatalogService::allProducts/productsBySlugs) or
    array (CatalogProductRef shape from mergedCatalogSections' panel.section.products)
    — the source's ProductCard accepts the same union, reading slug/name/code/hasModelCode
    off either. Normalized to array here so both shapes work identically.
--}}
@props(['product'])
@php
    $p = is_object($product) ? (array) $product : $product;
@endphp
<x-card route="products.show" :params="['slug' => $p['slug']]" class="group">
    <x-product-image
        :alt="$p['name']"
        :src="\App\Support\ProductImages::productImageSrc(['slug' => $p['slug'], 'image' => $p['image'] ?? null])"
        image-padding="p-7"
        :auto-frame="true"
    />
    <div class="px-5.5 py-5">
        @if ($p['hasModelCode'])
            <x-eyebrow class="mb-2">{{ $p['code'] }}</x-eyebrow>
        @endif
        <h3 class="font-heading font-bold text-base text-body leading-snug">
            {{ $p['name'] }}
        </h3>
    </div>
</x-card>
