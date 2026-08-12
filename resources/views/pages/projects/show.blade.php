<x-layouts.app :title="$metaTitle">
    <x-page-container class="pt-12">
        <x-breadcrumb :items="$breadcrumb" />
        <div class="text-xs font-bold tracking-wide uppercase text-primary mb-3">
            {{ $project['industry'] }}
        </div>
        <h1 class="font-heading font-bold text-[clamp(32px,4vw,44px)] text-heading leading-tight max-w-[900px]">
            {{ $project['name'] }}
        </h1>
        @if (! empty($project['location']))
            <p class="text-base text-muted mt-3">{{ $project['location'] }}</p>
        @endif
    </x-page-container>

    <x-page-container class="pt-8 pb-20">
        <div class="grid grid-cols-1 tablet:grid-cols-[1fr_300px] gap-12 items-start">
            <div>
                <x-project-image
                    :src="$project['image']"
                    :alt="$project['name']"
                    aspect-ratio="21/9"
                    class="rounded-2xl border border-border-light"
                />
                <p class="text-base leading-relaxed text-muted mt-8">
                    {{ $project['description'] }}
                </p>

                <div class="bg-surface border border-[#f7eeef] rounded-2xl p-9 mt-10">
                    <h2 class="font-heading font-bold text-[22px] text-heading mb-6">
                        {{ __('ProjectDetail.infoTitle') }}
                    </h2>
                    <dl class="grid grid-cols-1 tablet:grid-cols-2 gap-7 tablet:gap-x-10">
                        @if (! empty($project['location']))
                            <div>
                                <dt class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('ProjectDetail.location') }}</dt>
                                <dd class="text-[15px] leading-relaxed text-muted">{{ $project['location'] }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('ProjectDetail.industry') }}</dt>
                            <dd class="text-[15px] leading-relaxed text-muted">{{ $project['industry'] }}</dd>
                        </div>
                        <div class="tablet:col-span-2">
                            <dt class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('ProjectDetail.features') }}</dt>
                            <dd class="text-[15px] leading-relaxed text-muted">{{ $project['features'] }}</dd>
                        </div>
                        <div class="tablet:col-span-2">
                            <dt class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('ProjectDetail.tasks') }}</dt>
                            <dd class="text-[15px] leading-relaxed text-muted">{{ $project['tasks'] }}</dd>
                        </div>
                        <div class="tablet:col-span-2">
                            <dt class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('ProjectDetail.benefits') }}</dt>
                            <dd class="text-[15px] leading-relaxed text-muted">{{ $project['benefits'] }}</dd>
                        </div>
                    </dl>
                </div>

                <x-project-equipment-section :description="$project['equipment']" :products="$equipmentProducts" />

                <nav class="flex flex-col tablet:flex-row justify-between gap-5 mt-16 pt-8 border-t border-border-light">
                    @if ($prev)
                        <a href="{{ route('projects.show', ['slug' => $prev['slug']]) }}" class="flex flex-col gap-1.5 font-bold text-body p-5 border border-border-mid rounded-lg flex-1 max-w-full tablet:max-w-[48%] hover:border-primary hover:text-primary hover:shadow-[0_8px_20px_rgba(38,51,60,0.06)] no-underline transition-[color,border-color,box-shadow] duration-200">
                            <span class="text-xs font-semibold text-subtle uppercase tracking-wide">{{ __('ProjectDetail.previous') }}</span>
                            {{ $prev['name'] }}
                        </a>
                    @else
                        <div class="flex-1"></div>
                    @endif
                    @if ($next)
                        <a href="{{ route('projects.show', ['slug' => $next['slug']]) }}" class="flex flex-col gap-1.5 font-bold text-body p-5 border border-border-mid rounded-lg flex-1 max-w-full tablet:max-w-[48%] text-right items-end hover:border-primary hover:text-primary hover:shadow-[0_8px_20px_rgba(38,51,60,0.06)] no-underline transition-[color,border-color,box-shadow] duration-200">
                            <span class="text-xs font-semibold text-subtle uppercase tracking-wide">{{ __('ProjectDetail.next') }}</span>
                            {{ $next['name'] }}
                        </a>
                    @endif
                </nav>
            </div>

            <aside class="tablet:sticky tablet:top-[88px]">
                <h2 class="font-heading font-bold text-lg text-heading mb-4.5">
                    {{ __('ProjectDetail.otherProjects') }}
                </h2>
                @foreach ($featured as $item)
                    <x-reveal :delay="min($loop->index, 8) * 60">
                        <a
                            href="{{ route('projects.show', ['slug' => $item['slug']]) }}"
                            class="flex gap-3.5 p-3.5 rounded-lg border border-border-light bg-white mb-3 transition-[transform,border-color,box-shadow] duration-200 no-underline hover:shadow-card-sm hover:-translate-y-0.5 hover:border-[#f2d8db] {{ $item['slug'] === $currentSlug ? 'bg-surface-alt border-[#f2d8db] pointer-events-none' : '' }}"
                        >
                            <div class="w-[68px] h-[52px] shrink-0 rounded-sm overflow-hidden relative bg-surface">
                                <x-project-image :src="$item['image']" :alt="$item['name']" aspect-ratio="68/52" class="h-full w-full" />
                            </div>
                            <div>
                                <div class="font-bold text-sm text-heading leading-snug">{{ $item['name'] }}</div>
                                <div class="text-xs leading-snug text-muted mt-1">{{ $item['industry'] }}</div>
                            </div>
                        </a>
                    </x-reveal>
                @endforeach
            </aside>
        </div>
    </x-page-container>
</x-layouts.app>
