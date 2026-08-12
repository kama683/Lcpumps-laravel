<x-page-container class="pt-20">
    <div class="bg-gradient-to-br from-surface-alt to-[#f9e8ea] border border-[#f2d8db] rounded-3xl overflow-hidden grid grid-cols-1 tablet:grid-cols-2 items-center gap-8 p-10 tablet:px-16 tablet:py-14 max-tablet:text-center">
        <div>
            <h2 class="display-heading font-heading font-bold text-[clamp(26px,3vw,34px)] text-heading leading-tight">{{ __('HomeCta.title') }}</h2>
            <p class="text-base leading-relaxed text-muted mt-4 max-w-[440px] max-tablet:mx-auto">{{ __('HomeCta.description') }}</p>
            <x-button route="contact.index" class="mt-7 max-tablet:mx-auto">{{ __('HomeCta.button') }}</x-button>
        </div>
        <div class="relative flex items-center justify-center max-tablet:order-first">
            <div aria-hidden="true" class="hero-blueprint-grid absolute left-1/2 top-1/2 aspect-square w-[min(420px,90%)] -translate-x-1/2 -translate-y-1/2"></div>
            <img
                src="/assets/pump without background.png"
                alt="Промышленный насос"
                width="1666"
                height="944"
                loading="lazy"
                class="relative z-1 w-[min(420px,100%)] h-auto object-contain opacity-90"
            />
        </div>
    </div>
</x-page-container>
