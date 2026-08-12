<x-layouts.app :title="$metaTitle">
    <section class="bg-gradient-to-b from-surface to-white">
        <x-page-container class="py-14 pb-6">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="text-[13px] font-bold tracking-[1.5px] uppercase text-primary mt-6">
                {{ __('Nav.aboutUs') }}
            </div>
            <h1 class="font-heading font-bold text-[clamp(28px,4.5vw,42px)] text-heading mt-2.5 leading-tight">
                {{ $title }}
            </h1>
        </x-page-container>
    </section>
    <x-page-container class="py-6 pb-20">
        @foreach ($paragraphs as $paragraph)
            <p class="text-base leading-relaxed text-muted mb-5">{{ $paragraph }}</p>
        @endforeach
        @unless ($isPrimary)
            <x-button route="about-us.index" class="mt-4">
                {{ __('AboutSubPage.button') }}
            </x-button>
        @endunless
        <div class="mt-10">
            <a href="{{ route('about-us.index') }}" class="font-bold text-primary">
                {{ __('AboutSubPage.backLink') }}
            </a>
        </div>
    </x-page-container>
</x-layouts.app>
