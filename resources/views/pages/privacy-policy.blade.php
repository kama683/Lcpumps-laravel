@php
    $sectionOneParagraph = explode('lcpumps.kz', __('PrivacyPolicy.sections.01.p1'), 2);
    $canonicalUrl = app(\App\Services\SeoMetadataService::class)
        ->absoluteUrlForRoute('privacy-policy', [], app()->getLocale());
    $sectionHeadingClass = 'font-heading font-bold text-[clamp(22px,3vw,28px)] leading-tight text-heading';
    $paragraphClass = 'mt-4 text-[16px] leading-[1.75] text-muted';
    $listClass = 'mt-4 space-y-2.5 pl-5 text-[16px] leading-[1.7] text-muted marker:text-primary';
@endphp

<x-layouts.app
    :title="__('PrivacyPolicy.meta.title')"
    :meta-description="__('PrivacyPolicy.meta.description')"
    :canonical-url="$canonicalUrl"
>
    <section class="relative overflow-hidden border-b border-[#f7eeef] bg-gradient-to-b from-surface to-white">
        <x-hero-bg-image variant="page" />
        <x-page-container narrow class="relative z-10 py-12 tablet:py-16">
            <x-breadcrumb :items="[
                ['label' => __('PrivacyPolicy.breadcrumb.home'), 'route' => 'home'],
                ['label' => __('PrivacyPolicy.breadcrumb.current')],
            ]" />
            <x-eyebrow class="mt-6">{{ __('PrivacyPolicy.badge') }}</x-eyebrow>
            <h1 class="mt-2.5 font-heading text-[clamp(32px,5vw,48px)] font-bold leading-tight text-heading">
                {{ __('PrivacyPolicy.title') }}
            </h1>
            <p class="mt-5 max-w-[680px] text-[17px] leading-relaxed text-muted">
                {{ __('PrivacyPolicy.lead') }}
            </p>
        </x-page-container>
    </section>

    <x-page-container narrow class="py-12 tablet:py-16">
        <article class="divide-y divide-border-light">
            <section aria-labelledby="privacy-section-1" class="pb-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">01</span>
                    <div>
                        <h2 id="privacy-section-1" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.01.title') }}</h2>
                        <p class="{{ $paragraphClass }}">
                            {{ $sectionOneParagraph[0] }}<a href="https://lcpumps.kz" class="font-semibold">lcpumps.kz</a>{{ $sectionOneParagraph[1] }}
                        </p>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.01.p2') }}</p>
                    </div>
                </div>
            </section>

            <section aria-labelledby="privacy-section-2" class="py-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">02</span>
                    <div>
                        <h2 id="privacy-section-2" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.02.title') }}</h2>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.02.intro') }}</p>
                        <ul class="{{ $listClass }} list-disc">
                            @foreach (__('PrivacyPolicy.sections.02.items') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.02.outro') }}</p>
                    </div>
                </div>
            </section>

            <section aria-labelledby="privacy-section-3" class="py-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">03</span>
                    <div>
                        <h2 id="privacy-section-3" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.03.title') }}</h2>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.03.intro') }}</p>
                        <ul class="{{ $listClass }} list-disc">
                            @foreach (__('PrivacyPolicy.sections.03.items') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>

            <section aria-labelledby="privacy-section-4" class="py-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">04</span>
                    <div>
                        <h2 id="privacy-section-4" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.04.title') }}</h2>
                        <div class="mt-5 rounded-xl border border-[#f2d8db] bg-surface-alt/60 p-5 tablet:p-6">
                            <p class="text-[16px] leading-[1.75] text-muted">{{ __('PrivacyPolicy.sections.04.intro') }}</p>
                        </div>
                        <ul class="{{ $listClass }} list-disc">
                            @foreach (__('PrivacyPolicy.sections.04.items') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>

            <section aria-labelledby="privacy-section-5" class="py-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">05</span>
                    <div>
                        <h2 id="privacy-section-5" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.05.title') }}</h2>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.05.p1') }}</p>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.05.p2') }}</p>
                    </div>
                </div>
            </section>

            <section aria-labelledby="privacy-section-6" class="py-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">06</span>
                    <div>
                        <h2 id="privacy-section-6" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.06.title') }}</h2>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.06.p1') }}</p>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.06.p2') }}</p>
                    </div>
                </div>
            </section>

            <section aria-labelledby="privacy-section-7" class="py-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">07</span>
                    <div>
                        <h2 id="privacy-section-7" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.07.title') }}</h2>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.07.intro') }}</p>
                        <ul class="{{ $listClass }} list-disc">
                            @foreach (__('PrivacyPolicy.sections.07.items') as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.07.outro') }}</p>
                    </div>
                </div>
            </section>

            <section aria-labelledby="privacy-section-8" class="pt-10">
                <div class="flex items-start gap-4">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-surface-alt text-sm font-bold text-primary">08</span>
                    <div class="min-w-0">
                        <h2 id="privacy-section-8" class="{{ $sectionHeadingClass }}">{{ __('PrivacyPolicy.sections.08.title') }}</h2>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.08.intro') }}</p>
                        <div class="mt-5 rounded-xl border border-border-light bg-surface p-5 tablet:p-6">
                            <dl class="grid gap-4 text-[15px] tablet:grid-cols-[110px_1fr]">
                                <dt class="font-semibold text-heading">{{ __('PrivacyPolicy.contacts.company') }}</dt>
                                <dd class="text-muted">LC PUMPS</dd>
                                <dt class="font-semibold text-heading">{{ __('PrivacyPolicy.contacts.email') }}</dt>
                                <dd class="min-w-0"><a href="mailto:info@lcpumps.kz" class="break-all font-semibold">info@lcpumps.kz</a></dd>
                                <dt class="font-semibold text-heading">{{ __('PrivacyPolicy.contacts.website') }}</dt>
                                <dd class="min-w-0"><a href="https://lcpumps.kz" class="break-all font-semibold">https://lcpumps.kz</a></dd>
                                <dt class="font-semibold text-heading">{{ __('PrivacyPolicy.contacts.phone') }}</dt>
                                <dd><a href="tel:+77717786877" class="font-semibold">+7 771 778 68 77</a></dd>
                            </dl>
                        </div>
                        <p class="{{ $paragraphClass }}">{{ __('PrivacyPolicy.sections.08.footer') }}</p>
                    </div>
                </div>
            </section>
        </article>
    </x-page-container>
</x-layouts.app>
