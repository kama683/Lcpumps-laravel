<x-layouts.app :title="$metaTitle">
    <section class="bg-gradient-to-b from-surface to-white">
        <x-page-container class="py-14 pb-6">
            <x-breadcrumb :items="$breadcrumb" />
            <h1 class="font-heading font-bold text-[clamp(28px,4.5vw,42px)] text-heading mt-6 leading-tight">
                {{ __('Applications.waterWastewaterLink') }}
            </h1>
        </x-page-container>
    </section>
    <x-page-container class="py-6 pb-20">
        <p class="text-base leading-relaxed text-muted">{{ $text }}</p>
        <div class="mt-8 flex gap-6 flex-wrap">
            <div class="flex items-center gap-3">
                <x-icon-box icon="droplets" size="sm" />
                <x-arrow-link route="products.water">{{ __('Applications.waterEquipmentLink') }}</x-arrow-link>
            </div>
            <div class="flex items-center gap-3">
                <x-icon-box icon="waves" size="sm" />
                <x-arrow-link route="products.index" hash="wastewater">{{ __('Applications.wastewaterPumpsLink') }}</x-arrow-link>
            </div>
        </div>
    </x-page-container>
</x-layouts.app>
