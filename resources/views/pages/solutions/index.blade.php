<x-layouts.app :title="$metaTitle">
    <section class="bg-gradient-to-b from-surface to-white">
        <x-page-container class="py-14 pb-6">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="text-[13px] font-bold tracking-[1.5px] uppercase text-primary mt-6">
                {{ __('Solutions.eyebrow') }}
            </div>
            <h1 class="font-heading font-bold text-[clamp(28px,4.5vw,42px)] text-heading mt-2.5 leading-tight">
                {{ __('CatalogNav.water-supply-complete') }}
            </h1>
        </x-page-container>
    </section>
    <x-page-container class="py-8 pb-20">
        <div class="grid grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-3 gap-6 max-mobile:grid-cols-1">
            @foreach ($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </x-page-container>
</x-layouts.app>
