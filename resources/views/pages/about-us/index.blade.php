<x-layouts.app :title="$metaTitle">
    <section class="relative bg-white overflow-hidden">
        <x-page-container class="relative z-10 py-12 pb-14">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="relative aspect-video overflow-hidden rounded-2xl border border-border-light shadow-card-sm mt-7">
                <x-about-video photo-src="/assets/hero/lainc.jpg" photo-alt="LCPumps" />
            </div>

            <div class="mt-10">
                <h1 class="font-heading font-bold text-[clamp(32px,4.2vw,48px)] text-heading leading-tight">
                    {{ __('AboutUs.title') }}
                </h1>
                @foreach (__('AboutUs.intro') as $index => $paragraph)
                    <p class="text-[17px] leading-relaxed text-muted {{ $index === 0 ? 'mt-6' : 'mt-5' }}">
                        {{ $paragraph }}
                    </p>
                @endforeach
            </div>
        </x-page-container>
    </section>

    <section class="bg-surface border-y border-[#f7eeef] py-14">
        <x-page-container>
            <x-stat-grid :items="$stats" />
        </x-page-container>
    </section>

    <x-page-container class="pt-16">
        <x-reveal>
            <h2 class="font-heading font-bold text-[clamp(26px,3vw,34px)] text-heading">
                {{ __('AboutUs.certificationsTitle') }}
            </h2>
            <p class="text-base leading-relaxed text-muted mt-4.5 max-w-[960px]">
                {{ $cert }}
            </p>
            <div class="mt-6 flex flex-wrap gap-2.5">
                @foreach (\App\Support\Content::ABOUT_CERTIFICATIONS as $item)
                    <span class="inline-block bg-white text-muted text-sm font-semibold px-4.5 py-2.5 rounded-pill border border-border-mid transition-[border-color,color,transform] duration-200 hover:-translate-y-0.5 hover:border-primary hover:text-primary">
                        {{ $item }}
                    </span>
                @endforeach
            </div>
            <p class="text-base leading-relaxed text-muted mt-8">
                {{ __('AboutUs.awardsIntro') }}
            </p>
            <div class="mt-5 flex flex-wrap gap-2.5">
                @foreach ($awards as $item)
                    <span class="inline-block bg-pill-blue-bg text-primary text-sm font-semibold px-4.5 py-2.5 rounded-pill transition-[background-color,color,transform] duration-200 hover:-translate-y-0.5 hover:bg-primary hover:text-white">
                        {{ $item }}
                    </span>
                @endforeach
            </div>
            <x-arrow-link route="about-us.show" :params="['slug' => 'certifications']" class="mt-8">
                {{ __('AboutUs.certificationsLink') }}
            </x-arrow-link>
        </x-reveal>
    </x-page-container>

    <x-page-container class="pt-18 text-center">
        <x-reveal>
            <h2 class="font-heading font-bold text-[clamp(24px,3vw,32px)] text-heading">
                {{ __('AboutUs.clientsTitle') }}
            </h2>
            <p class="text-base leading-relaxed text-muted mt-4 max-w-[720px] mx-auto">
                {{ $clients }}
            </p>
            <div class="flex flex-wrap justify-center items-end gap-x-12 gap-y-10 mt-12">
                @foreach (\App\Support\Content::ABOUT_CLIENTS as $client)
                    <div class="flex flex-col items-center gap-3.5 min-w-[110px] transition-transform duration-300 hover:-translate-y-0.5">
                        <img
                            src="{{ $client['logo'] }}"
                            alt="{{ $client['name'] }}"
                            width="120"
                            height="48"
                            loading="lazy"
                            class="h-11 w-auto max-w-[130px] object-contain opacity-55 grayscale transition-[opacity,filter] duration-300 hover:opacity-100 hover:grayscale-0"
                        />
                        <span class="font-heading font-bold text-lg text-muted-light">{{ $client['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-reveal>
    </x-page-container>

    <x-page-container class="py-18 pb-20">
        <x-reveal>
            <div class="bg-gradient-to-br from-surface-alt to-[#f8e4e6] border border-[#f2d8db] rounded-3xl p-7 tablet:p-12 tablet:px-14 grid grid-cols-1 tablet:grid-cols-[1.1fr_0.9fr] gap-8 tablet:gap-10 items-center max-tablet:text-center">
                <div>
                    <h2 class="font-heading font-bold text-[clamp(24px,3vw,32px)] text-heading leading-tight">
                        {{ \App\Support\Site::COMPANY_NAME }}
                    </h2>
                    <p class="text-base leading-relaxed text-muted mt-4">{{ $service }}</p>
                    <p class="text-[15px] leading-snug text-muted-light mt-3">
                        {{ __('Common.headquartersNote') }}
                    </p>
                    <x-button route="contact.index" class="mt-7 max-tablet:mx-auto">
                        {{ __('Common.contactUs') }}
                    </x-button>
                </div>
                <div class="relative flex items-center justify-center max-tablet:order-first">
                    <div
                        aria-hidden="true"
                        class="hero-blueprint-grid absolute left-1/2 top-1/2 aspect-square w-[min(420px,90%)] -translate-x-1/2 -translate-y-1/2"
                    ></div>
                    <img
                        src="/assets/pump without background.png"
                        alt="LCPumps industrial pump technical illustration"
                        width="1666"
                        height="944"
                        loading="lazy"
                        class="relative z-1 w-[min(420px,100%)] h-auto object-contain opacity-90"
                    />
                </div>
            </div>
        </x-reveal>
    </x-page-container>

    <x-page-container class="pb-16">
        <x-reveal>
            <x-section-heading :title="__('AboutUs.sectionsTitle')" />
            <div class="grid grid-cols-1 tablet:grid-cols-2 gap-4 mt-8">
                @foreach (['our-company', 'innovation', 'history', 'sustainability', 'production', 'certifications'] as $slug)
                    <a
                        href="{{ route('about-us.show', ['slug' => $slug]) }}"
                        class="group flex items-center justify-between gap-3 p-5 border border-border rounded-xl text-body font-bold hover:border-primary hover:text-primary no-underline transition-colors"
                    >
                        <span>{{ __('AboutUs.subnav.'.$slug) }}</span>
                        <x-icon name="chevron-right" stroke-width="2.25" class="size-5 shrink-0 text-primary transition-transform duration-200 group-hover:translate-x-1" />
                    </a>
                @endforeach
            </div>
        </x-reveal>
    </x-page-container>
</x-layouts.app>
