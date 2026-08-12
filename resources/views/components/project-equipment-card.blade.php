{{-- Ports components/projects/ProjectEquipmentSection.tsx's ProjectEquipmentCard. $product: stdClass from CatalogService::productsBySlugs(). --}}
@props(['product'])
<x-card route="products.show" :params="['slug' => $product->slug]" class="flex h-full flex-col hover:shadow-card-sm">
    <x-product-image
        :alt="$product->name"
        :src="\App\Support\ProductImages::productImageSrc(['slug' => $product->slug, 'image' => $product->image ?? null])"
        aspect-ratio="1/1"
        sizes="(max-width: 900px) 50vw, 22vw"
    />
    <div class="flex flex-1 flex-col p-3.5 pb-4">
        @if ($product->hasModelCode)
            <x-eyebrow class="mb-1 text-[11px]">{{ $product->code }}</x-eyebrow>
        @endif
        <h3 class="font-heading font-bold text-[13px] text-body leading-snug line-clamp-3 flex-1">
            {{ $product->name }}
        </h3>
        <span class="mt-3 inline-flex self-start rounded-sm border border-border-mid bg-white px-3 py-1.5 text-[11px] font-bold text-body transition-colors hover:border-primary hover:text-primary">
            {{ __('ProjectDetail.learnMore') }}
        </span>
    </div>
</x-card>
