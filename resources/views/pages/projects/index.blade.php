<x-layouts.app :title="$metaTitle">
    <section class="relative bg-white overflow-hidden">
        <x-hero-bg-image variant="page" />
        <x-page-container class="relative z-10 pt-12 pb-6">
            <x-breadcrumb :items="$breadcrumb" />
            <x-section-heading
                :eyebrow="__('Nav.projects')"
                :title="__('Projects.completedTitle')"
                :description="$projectsData['heroIntro']"
            />
        </x-page-container>
    </section>

    <x-page-container class="pb-12">
        <div class="grid grid-cols-1 tablet:grid-cols-2 gap-7 [&>*:last-child:nth-child(odd)]:tablet:col-span-2 [&>*:last-child:nth-child(odd)]:tablet:mx-auto [&>*:last-child:nth-child(odd)]:tablet:max-w-[calc(50%-14px)]">
            @foreach ($projectsData['featured'] as $project)
                <x-card route="projects.show" :params="['slug' => $project['slug']]">
                    <x-project-image :src="$project['image']" :alt="$project['name']" />
                    <div class="p-7 pb-7.5 flex flex-col flex-1">
                        <div class="text-xs font-bold tracking-wide uppercase text-primary mb-2">
                            {{ $project['industry'] }}
                        </div>
                        <h3 class="font-heading font-bold text-xl text-heading leading-snug">
                            {{ $project['name'] }}
                        </h3>
                        <p class="text-sm leading-relaxed text-muted mt-3 flex-1">
                            {{ $project['short'] }}
                        </p>
                    </div>
                </x-card>
            @endforeach
        </div>
    </x-page-container>

    <section class="bg-surface border-y border-[#f7eeef]">
        <x-page-container class="py-16">
            <x-reveal>
                <h2 class="font-heading font-bold text-[clamp(26px,3vw,34px)] text-heading">
                    {{ __('Projects.industriesTitle') }}
                </h2>
                <div class="grid grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-3 gap-6 mt-8 max-mobile:grid-cols-1">
                    @foreach ($projectsData['industries'] as $industry)
                        <x-reveal :delay="min($loop->index, 8) * 60" class="bg-white border border-border rounded-xl p-7 hover:shadow-card-sm hover:-translate-y-0.5 transition-[box-shadow,transform] duration-200">
                            <h3 class="font-heading font-bold text-xl text-heading">
                                {{ $industry['title'] }}
                            </h3>
                            <p class="text-sm leading-relaxed text-muted mt-3">
                                {{ $industry['intro'] }}
                            </p>
                            <x-bullet-list :items="$industry['items']" class="mt-4" />
                        </x-reveal>
                    @endforeach
                </div>
            </x-reveal>
        </x-page-container>
    </section>

    <x-page-container class="py-16 pb-20">
        <x-reveal>
            <h2 class="font-heading font-bold text-2xl text-heading mb-6">
                {{ __('Projects.otherSitesTitle') }}
            </h2>
            <div class="flex flex-wrap gap-2.5">
                @foreach ($projectsData['compact'] as $name)
                    <span class="inline-block px-4 py-2.5 rounded-pill border border-border-mid bg-white text-[13px] font-semibold text-muted transition-[border-color,color,transform] duration-200 hover:-translate-y-0.5 hover:border-primary hover:text-primary">
                        {{ $name }}
                    </span>
                @endforeach
            </div>
            <div class="mt-8">
                <x-arrow-link route="contact.index">{{ __('Projects.discussButton') }}</x-arrow-link>
            </div>
        </x-reveal>
    </x-page-container>
</x-layouts.app>
