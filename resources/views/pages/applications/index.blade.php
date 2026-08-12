<x-layouts.app :title="$metaTitle">
    <section class="bg-gradient-to-b from-surface to-white">
        <x-page-container class="py-14 pb-6">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="text-[13px] font-bold tracking-[1.5px] uppercase text-primary mt-6">
                {{ __('Applications.breadcrumbLabel') }}
            </div>
            <h1 class="font-heading font-bold text-[clamp(28px,4.5vw,42px)] text-heading mt-2.5 leading-tight">
                {{ __('Applications.title') }}
            </h1>
            <p class="text-base leading-relaxed text-muted mt-5 max-w-[880px]">
                {{ $intro }}
            </p>
        </x-page-container>
    </section>
    <x-page-container class="py-8 pb-20">
        <div class="grid grid-cols-1 tablet:grid-cols-2 gap-6">
            @foreach ($cards as $index => $card)
                <div class="card border border-border rounded-xl p-7">
                    <x-icon-box :icon="$cardIcons[$index] ?? 'layers'" size="sm" class="mb-4" />
                    <h3 class="font-heading font-bold text-xl text-body">
                        {{ $card['title'] }}
                    </h3>
                    <p class="text-[15px] leading-relaxed text-muted-light mt-3">
                        {{ $card['text'] }}
                    </p>
                </div>
            @endforeach
        </div>
        <div class="mt-10">
            <x-arrow-link route="applications.water-wastewater">
                {{ __('Applications.waterWastewaterLink') }}
            </x-arrow-link>
        </div>
    </x-page-container>
</x-layouts.app>
