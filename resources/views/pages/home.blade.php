<x-layouts.app :title="__('PageMetadata.home.title')" :meta-description="__('PageMetadata.home.description')">
    <section class="relative bg-heading overflow-hidden min-h-dvh">
        <x-hero-slider :images="$heroSlides" />
        <x-page-container class="relative z-10 pt-31 pb-16 tablet:pt-42 tablet:pb-22">
            <div class="max-w-[640px]">
                {{--
                    Was KNOWN_DEFECTS.md #1: the source has a stray extra
                    closing paren on this line's clamp utility, which broke
                    it. Fixed here — no longer preserved as-is, see
                    KNOWN_DEFECTS.md's changelog note. (Deliberately not
                    spelling out the old broken class text in this comment —
                    Tailwind's scanner picks up bracketed-looking substrings
                    from anywhere in the file, comments included, and kept
                    generating a dead rule for it.)
                --}}
                <h1
                    class="animate-hero-fade-up font-heading font-bold text-[clamp(30px,4vw,48px)] leading-[1.12] text-white"
                    style="animation-delay: 80ms"
                >
                    {{ __('Home.heroPrefix') }}
                    <span class="text-primary">{{ __('Home.heroHighlight') }}</span>
                    {{ __('Home.heroSuffix') }}
                </h1>
                <p class="animate-hero-fade-up text-[19px] leading-relaxed text-white/85 mt-6 max-w-[560px]" style="animation-delay: 140ms">
                    {{ __('Home.intro') }}
                </p>
                <div class="animate-hero-fade-up flex gap-4 mt-9 flex-wrap" style="animation-delay: 200ms">
                    <x-button route="contact.index">{{ __('Common.contactUs') }}</x-button>
                    <x-button route="products.index">{{ __('Home.catalogButton') }}</x-button>
                </div>
            </div>
        </x-page-container>
    </section>

    <x-page-container class="pt-16 pb-8 tablet:pt-20 tablet:pb-10">
        <x-stat-grid :items="$homeStats" :columns="5" :compact="true" />
    </x-page-container>

    <section class="bg-surface mt-8 border-y border-[#f7eeef]">
        <x-page-container class="py-18">
            <x-reveal>
                <div class="grid grid-cols-1 tablet:grid-cols-2 gap-12 items-center">
                    <div class="group relative aspect-4/3 overflow-hidden rounded-2xl border border-border-light shadow-card-sm">
                        <x-about-video
                            photo-src="/assets/hero/lainc.jpg"
                            photo-alt="LCPumps"
                            class="transition-transform duration-500 ease-out group-hover:scale-105"
                        />
                    </div>
                    <div>
                        <x-section-heading :eyebrow="__('Nav.aboutUs')" :title="\App\Support\Site::COMPANY_NAME" />
                        <p class="text-base leading-relaxed text-muted mt-4.5">{{ __('Home.companyParagraph') }}</p>
                        <x-button route="about-us.index" class="mt-6">{{ __('Home.aboutButton') }}</x-button>
                    </div>
                </div>
            </x-reveal>
        </x-page-container>
    </section>

    <x-page-container class="pt-20">
        <x-reveal>
            <x-section-heading
                :eyebrow="__('Home.productsEyebrow')"
                :title="__('Home.catalogTitle')"
                :description="\App\Support\Site::catalogIntro(app()->getLocale())"
                :center="true"
            />
            <div class="grid grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-3 gap-6 mt-10 max-mobile:grid-cols-1">
                @foreach ($featuredProducts as $index => $product)
                    <div data-reveal style="--reveal-offset: 0.75rem; transition-delay: {{ min($index, 8) * 60 }}ms">
                        <x-product-card :product="$product" />
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-8">
                <x-button route="products.index" variant="secondary" :arrow="true">{{ __('Home.wholeCatalogButton') }}</x-button>
            </div>
        </x-reveal>
    </x-page-container>

    <x-page-container class="pt-20">
        <x-reveal>
            <x-section-heading :eyebrow="__('Nav.projects')" :title="__('Projects.completedTitle')" />
            <div class="grid grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-3 gap-5 mt-8 max-mobile:grid-cols-1">
                @foreach ($featuredProjects as $index => $project)
                    <div data-reveal style="--reveal-offset: 0.75rem; transition-delay: {{ min($index, 8) * 60 }}ms">
                        <x-card route="projects.show" :params="['slug' => $project['slug']]">
                            <x-project-image :src="$project['image']" :alt="$project['name']" aspect-ratio="16/10" />
                            <div class="p-5.5">
                                <div class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ $project['industry'] }}</div>
                                <div class="font-bold text-body leading-snug">{{ $project['name'] }}</div>
                            </div>
                        </x-card>
                    </div>
                @endforeach
            </div>
            <div class="mt-7">
                <x-arrow-link route="projects.index">{{ __('Home.allProjectsLink') }}</x-arrow-link>
            </div>
        </x-reveal>
    </x-page-container>

    <x-reveal class="pb-20">
        <x-home-cta-section />
    </x-reveal>
</x-layouts.app>
