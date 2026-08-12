<x-layouts.app :title="$metaTitle">
    <section class="relative bg-gradient-to-b from-surface to-white overflow-hidden">
        <x-hero-bg-image variant="page" />
        <x-page-container class="relative z-10 py-14 pb-6">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="text-[13px] font-bold tracking-[1.5px] uppercase text-primary mt-6">
                {{ __('Services.label') }}
            </div>
            <h1 class="font-heading font-bold text-[clamp(28px,4.5vw,42px)] text-heading mt-2.5 leading-tight">
                {{ __('Services.title') }}
            </h1>
        </x-page-container>
    </section>
    <x-page-container class="py-6 pb-20">
        <x-icon-box icon="headphones" size="md" class="mb-6" />
        <p class="text-base leading-relaxed text-muted max-w-[720px]">{{ $servicePara }}</p>
        <x-button route="contact.index" class="mt-6" arrow>
            {{ __('Services.button') }}
        </x-button>
    </x-page-container>
</x-layouts.app>
