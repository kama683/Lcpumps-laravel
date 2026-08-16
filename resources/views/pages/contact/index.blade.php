@php
    $field = 'contact-input w-full py-3.5 px-4 border border-border-mid rounded-md text-[15px] text-body bg-white placeholder:text-subtle focus:outline-none focus:border-primary focus:shadow-[0_0_0_3px_rgba(247,6,32,0.12)] transition-[border-color,box-shadow]';

    $primaryTech = [
        ['model', __('Contact.form.primaryFields.model')],
        ['flow', __('Contact.form.primaryFields.flow')],
        ['head', __('Contact.form.primaryFields.head')],
        ['power', __('Contact.form.primaryFields.power')],
        ['speed', __('Contact.form.primaryFields.speed')],
        ['efficiency', __('Contact.form.primaryFields.efficiency')],
        ['temperature', __('Contact.form.primaryFields.temperature')],
        ['inlet_diameter', __('Contact.form.primaryFields.inletDiameter')],
        ['outlet_diameter', __('Contact.form.primaryFields.outletDiameter')],
        ['pump_material', __('Contact.form.primaryFields.pumpMaterial')],
    ];
    $extraTech = [
        ['motor', __('Contact.form.extraFields.motor')],
        ['voltage', __('Contact.form.extraFields.voltage')],
        ['protection_level', __('Contact.form.extraFields.protectionLevel')],
        ['cooling_method', __('Contact.form.extraFields.coolingMethod')],
        ['motor_efficiency', __('Contact.form.extraFields.motorEfficiency')],
        ['weight', __('Contact.form.extraFields.weight')],
    ];
@endphp
<x-layouts.app :title="$metaTitle">
    <section class="relative bg-surface overflow-hidden border-b border-[#f7eeef]">
        <x-hero-bg-image variant="contact" />
        <x-page-container class="relative z-10 py-10 pb-24">
            <x-breadcrumb :items="$breadcrumb" />
            <div class="max-w-[640px] mt-6">
                <div class="inline-block text-xs font-bold tracking-[1.4px] uppercase text-primary bg-white border border-[#f7e3e5] px-4.5 py-2.5 rounded-pill mb-6">
                    {{ __('Nav.contacts') }}
                </div>
                <h1 class="font-heading font-bold text-[clamp(36px,4.5vw,52px)] text-heading leading-tight">
                    {{ __('ContactPage.title') }}
                </h1>
                <div class="inline-flex items-center gap-2 rounded-pill bg-primary px-4.5 py-2.5 mt-5 text-sm font-bold text-white shadow-btn">
                    <x-icon name="clock" stroke-width="2.5" class="size-4 shrink-0" />
                    {{ __('ContactPage.responseTime') }}
                </div>
                <p class="text-lg leading-relaxed text-muted mt-5">
                    {{ __('ContactPage.intro') }}
                </p>
            </div>
        </x-page-container>
    </section>

    <section class="max-w-[var(--container-content)] mx-auto px-8 -mt-6 pb-16 relative z-1">
        <div class="max-w-[1160px] mx-auto bg-white border border-[#f2d8db] rounded-3xl shadow-contact p-8 tablet:p-12 tablet:px-14">
            <div class="mb-9 max-w-[640px]">
                <div class="text-xs font-bold tracking-[1.4px] uppercase text-primary mb-3">{{ __('Contact.form.eyebrow') }}</div>
                <h2 class="display-heading font-heading font-bold text-[clamp(24px,2.8vw,32px)] text-heading leading-tight">{{ __('Contact.form.title') }}</h2>
                <p class="text-[15px] leading-relaxed text-muted mt-3">{{ __('Contact.form.intro') }}</p>
            </div>

            <form data-contact-form method="POST" action="{{ route('contact.submit') }}" enctype="multipart/form-data" class="space-y-0">
                @csrf
                <input type="hidden" name="mode" data-contact-mode-input value="{{ $techOpen ? 'technical' : 'basic' }}">
                {{-- Honeypot — hidden from real visitors, only bots that fill every field trip it. --}}
                <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="absolute left-[-9999px] top-auto size-px overflow-hidden">

                {{-- Block 1 — contacts --}}
                <div class="mb-8 pb-8 border-b border-[#f0e2e4]">
                    <div class="font-bold text-base text-heading mb-4.5">{{ __('Contact.form.contactDataSection') }}</div>
                    <div class="grid grid-cols-1 tablet:grid-cols-2 gap-4 mb-4">
                        <input class="{{ $field }}" type="text" name="name" placeholder="{{ __('Contact.form.fields.name') }}" value="{{ old('name') }}">
                        <input class="{{ $field }}" type="text" name="company" placeholder="{{ __('Contact.form.fields.company') }}" value="{{ old('company') }}">
                    </div>
                    <div class="grid grid-cols-1 tablet:grid-cols-2 gap-4">
                        <input class="{{ $field }}" type="email" name="email" placeholder="{{ __('Contact.form.fields.email') }}" value="{{ old('email') }}">
                        <input class="{{ $field }}" type="tel" name="phone" placeholder="{{ __('Contact.form.fields.phone') }}" value="{{ old('phone') }}">
                    </div>
                </div>

                {{-- Block 2 — message --}}
                <div class="{{ $techOpen ? 'mb-8 pb-8 border-b border-[#f0e2e4]' : 'pb-8 border-b border-[#f0e2e4] mb-6' }}">
                    <div class="font-bold text-base text-heading mb-4.5">{{ __('Contact.form.messageSection') }}</div>
                    <textarea class="{{ $field }} min-h-[180px] resize-y" name="message" placeholder="{{ __('Contact.form.messagePlaceholder') }}" rows="6">{{ old('message') }}</textarea>
                </div>

                {{-- Tech CTA — visible when closed --}}
                <div data-tech-cta @if ($techOpen) hidden @endif class="mt-2 mb-8 rounded-2xl border border-[#f2d8db] bg-gradient-to-br from-surface to-surface-alt p-7 tablet:p-9">
                    <div class="flex flex-col tablet:flex-row tablet:items-center gap-6">
                        <x-icon-box icon="settings-2" size="lg" class="shrink-0" />
                        <div class="flex-1 min-w-0">
                            <h3 class="font-heading font-bold text-xl text-heading leading-snug">{{ __('Contact.form.techCta.title') }}</h3>
                            <p class="text-[15px] leading-relaxed text-muted mt-2 max-w-[560px]">{{ __('Contact.form.techCta.description') }}</p>
                        </div>
                        <button
                            type="button"
                            data-tech-open-btn
                            aria-expanded="{{ $techOpen ? 'true' : 'false' }}"
                            class="shrink-0 inline-flex items-center justify-center gap-2 bg-white text-primary font-bold text-[15px] px-6 py-3.5 rounded-sm border border-[#eac5c9] cursor-pointer transition-[background,border-color,transform] duration-200 hover:border-primary hover:bg-[#fcf4f5] hover:-translate-y-0.5"
                        >
                            {{ __('Contact.form.techCta.button') }}
                            <x-icon name="chevron-down" stroke-width="2.25" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- Engineering section — progressive disclosure --}}
                <div data-tech-panel class="grid overflow-clip transition-[grid-template-rows] duration-500 ease-(--ease-entrance) {{ $techOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div data-tech-panel-inner class="min-h-0 min-w-0 {{ $techOpen ? 'overflow-visible' : 'overflow-clip [content-visibility:hidden]' }}">
                        <div class="pb-2 pt-1">
                            <div class="flex items-start justify-between gap-4 mb-7 pb-6 border-b border-[#f0e2e4]">
                                <div>
                                    <div class="text-xs font-bold tracking-[1.4px] uppercase text-primary mb-2">{{ __('Contact.form.engineering.eyebrow') }}</div>
                                    <h3 class="font-heading font-bold text-[clamp(20px,2.2vw,26px)] text-heading">{{ __('Contact.form.engineering.title') }}</h3>
                                    <p class="text-[14px] text-muted mt-2 leading-relaxed">{{ __('Contact.form.engineering.description') }}</p>
                                </div>
                                <button
                                    type="button"
                                    data-tech-collapse-btn
                                    aria-expanded="{{ $techOpen ? 'true' : 'false' }}"
                                    class="shrink-0 text-sm font-semibold text-muted hover:text-primary cursor-pointer bg-transparent border-none px-2 py-1"
                                >{{ __('Contact.form.engineering.collapse') }}</button>
                            </div>

                            <div class="mb-6">
                                <div class="font-bold text-base text-heading mb-4.5">{{ __('Contact.form.primaryParams') }}</div>
                                @for ($i = 0; $i < count($primaryTech); $i += 2)
                                    <div class="grid grid-cols-1 tablet:grid-cols-2 gap-4 {{ $i + 2 < count($primaryTech) ? 'mb-4' : '' }}">
                                        @foreach (array_slice($primaryTech, $i, 2) as [$name, $label])
                                            <input class="{{ $field }}" type="text" name="{{ $name }}" placeholder="{{ $label }}" value="{{ old($name) }}">
                                        @endforeach
                                    </div>
                                @endfor
                            </div>

                            <x-accordion-details :title="__('Contact.form.extraParams')" class="mt-2 mb-8">
                                @for ($i = 0; $i < count($extraTech); $i += 2)
                                    <div class="grid grid-cols-1 tablet:grid-cols-2 gap-4 {{ $i + 2 < count($extraTech) ? 'mb-4' : '' }}">
                                        @foreach (array_slice($extraTech, $i, 2) as [$name, $label])
                                            <input class="{{ $field }}" type="text" name="{{ $name }}" placeholder="{{ $label }}" value="{{ old($name) }}">
                                        @endforeach
                                    </div>
                                @endfor
                            </x-accordion-details>

                            {{-- Engineering parameter table --}}
                            <div class="rounded-2xl border border-[#f2d8db] bg-surface overflow-hidden">
                                <div class="px-7 pt-7 pb-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <x-icon-box icon="table-2" size="sm" />
                                        <span class="text-xs font-bold tracking-[1.2px] uppercase text-primary">{{ __('Contact.form.paramsTable.eyebrow') }}</span>
                                    </div>
                                    <h4 class="font-heading font-bold text-lg text-heading">{{ __('Contact.form.paramsTable.title') }}</h4>
                                    <p class="text-[14px] leading-relaxed text-muted mt-2 max-w-[640px]">{{ __('Contact.form.paramsTable.description') }}</p>
                                </div>
                                <div class="mx-7 mb-7">
                                    <x-pump-dimensions-table />
                                </div>
                            </div>

                            {{-- Pump scheme --}}
                            <div class="rounded-2xl border border-[#f2d8db] bg-surface overflow-hidden mt-5">
                                <div class="px-7 pt-7 pb-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <x-icon-box icon="waypoints" size="sm" />
                                        <span class="text-xs font-bold tracking-[1.2px] uppercase text-primary">{{ __('Contact.form.scheme.eyebrow') }}</span>
                                    </div>
                                    <h4 class="font-heading font-bold text-lg text-heading">{{ __('Contact.form.scheme.title') }}</h4>
                                    <p class="text-[14px] leading-relaxed text-muted mt-2 max-w-[640px]">{{ __('Contact.form.scheme.description') }}</p>
                                </div>
                                <div class="mx-7 mb-7 rounded-xl border border-[#e8cfd2] bg-white p-4">
                                    <div class="relative w-full aspect-[1920/816]">
                                        <img src="/assets/plan.png" alt="{{ __('Contact.form.scheme.alt') }}" class="absolute inset-0 size-full object-contain">
                                    </div>
                                </div>
                            </div>

                            {{-- Upload --}}
                            <div data-upload-dropzone class="mt-8 rounded-2xl border-2 border-dashed border-[#ecd0d3] bg-surface px-8 py-12 text-center transition-[border-color,background,box-shadow] duration-200">
                                <div data-upload-icon class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-white border border-[#f2d8db] text-primary mb-5 transition-transform duration-200">
                                    <x-icon name="file-up" stroke-width="1.75" class="size-7" />
                                </div>
                                <div class="font-heading font-bold text-lg text-heading">{{ __('Contact.form.upload.title') }}</div>
                                <p class="text-[15px] text-muted mt-2">
                                    {{ __('Contact.form.upload.or') }}
                                    <label class="text-primary font-semibold cursor-pointer hover:text-primary-dark underline-offset-2 hover:underline">
                                        {{ __('Contact.form.upload.chooseFile') }}
                                        <input
                                            data-upload-input
                                            type="file"
                                            name="attachments[]"
                                            multiple
                                            class="sr-only"
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.dwg,.png,.jpg,.jpeg"
                                        >
                                    </label>
                                </p>
                                <p class="text-[13px] text-subtle mt-4">{{ __('Contact.form.upload.hint') }}</p>
                                <ul data-upload-filelist class="hidden mt-5 flex flex-wrap justify-center gap-2"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($successMessage)
                    <p class="mb-4 flex translate-y-0 items-center justify-center gap-2 rounded-md bg-[#eef8f0] px-4 py-3 text-sm font-semibold text-success opacity-100 transition-[opacity,transform] duration-250 ease-(--ease-entrance)">
                        <x-icon name="check-circle-2" stroke-width="2.25" class="size-4 shrink-0" />
                        {{ __('Contact.form.success') }}
                    </p>
                @endif
                @if ($errorMessage)
                    <p class="mb-4 flex translate-y-0 items-center justify-center gap-2 rounded-md bg-[#fdeeee] px-4 py-3 text-sm font-semibold text-error opacity-100 transition-[opacity,transform] duration-250 ease-(--ease-entrance)">
                        <x-icon name="alert-circle" stroke-width="2.25" class="size-4 shrink-0" />
                        {{ $errorMessage }}
                    </p>
                @endif

                <button type="submit" class="w-full mt-2 bg-primary text-white font-bold text-base py-4.5 rounded-sm border-none cursor-pointer shadow-btn transition-[background,transform] duration-200 hover:bg-primary-dark hover:-translate-y-0.5 disabled:cursor-default disabled:opacity-60 disabled:hover:translate-y-0">
                    {{ __('Contact.form.submit') }}
                </button>
                <p class="text-[13px] text-subtle text-center mt-4 leading-snug">{{ __('Contact.form.submitHint') }}</p>
            </form>
        </div>
    </section>

    {{-- Direct contacts --}}
    <section class="max-w-[var(--container-content)] mx-auto px-8 pb-20">
        <div class="text-center max-w-[560px] mx-auto mb-9">
            <h2 class="font-heading font-bold text-[clamp(24px,2.8vw,32px)] text-heading">{{ __('Contact.direct.title') }}</h2>
            <p class="text-base text-muted mt-3 leading-relaxed">{{ __('Contact.direct.description') }}</p>
        </div>
        <div class="grid grid-cols-1 max-tablet:grid-cols-2 tablet:grid-cols-4 gap-4 max-mobile:grid-cols-1">
            <div class="bg-white border border-border-light rounded-2xl p-6 min-h-[148px] flex flex-col hover:shadow-card-sm hover:-translate-y-0.5 hover:border-[#f2d8db] transition-[transform,border-color,box-shadow] duration-200">
                <x-icon-box icon="building-2" size="sm" class="mb-4" />
                <div class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('Contact.direct.address.label') }}</div>
                <div class="text-[15px] leading-relaxed text-muted [&_strong]:text-heading [&_strong]:font-bold [&_a]:font-semibold">
                    <strong>{{ \App\Support\Site::COMPANY_NAME }}</strong><br>{{ __('Contact.direct.address.value') }}
                </div>
            </div>
            <div class="bg-white border border-border-light rounded-2xl p-6 min-h-[148px] flex flex-col hover:shadow-card-sm hover:-translate-y-0.5 hover:border-[#f2d8db] transition-[transform,border-color,box-shadow] duration-200">
                <x-icon-box icon="mail" size="sm" class="mb-4" />
                <div class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('Contact.direct.email.label') }}</div>
                <div class="text-[15px] leading-relaxed text-muted [&_strong]:text-heading [&_strong]:font-bold [&_a]:font-semibold">
                    <a href="mailto:info@lcpumps.kz">info@lcpumps.kz</a>
                </div>
            </div>
            <div class="bg-white border border-border-light rounded-2xl p-6 min-h-[148px] flex flex-col hover:shadow-card-sm hover:-translate-y-0.5 hover:border-[#f2d8db] transition-[transform,border-color,box-shadow] duration-200">
                <x-icon-box icon="phone" size="sm" class="mb-4" />
                <div class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('Contact.direct.phone.label') }}</div>
                <div class="text-[15px] leading-relaxed text-muted [&_strong]:text-heading [&_strong]:font-bold [&_a]:font-semibold">
                    <strong>+7 771 778 68 77</strong>
                </div>
            </div>
            <div class="bg-white border border-border-light rounded-2xl p-6 min-h-[148px] flex flex-col hover:shadow-card-sm hover:-translate-y-0.5 hover:border-[#f2d8db] transition-[transform,border-color,box-shadow] duration-200">
                <x-icon-box icon="clock" size="sm" class="mb-4" />
                <div class="text-xs font-bold tracking-wide uppercase text-primary mb-2">{{ __('Contact.direct.hours.label') }}</div>
                <div class="text-[15px] leading-relaxed text-muted [&_strong]:text-heading [&_strong]:font-bold [&_a]:font-semibold">
                    {{ __('Contact.direct.hours.value') }}<br><span style="font-size:13px;color:#9aa5ac">{{ __('Contact.direct.hours.timezoneNote') }}</span>
                </div>
            </div>
        </div>
    </section>

    <x-page-container narrow class="pb-24">
        <h2 class="font-heading font-bold text-[clamp(26px,3vw,34px)] text-heading text-center mb-3">{{ __('Faq.title') }}</h2>
        <p class="text-center text-base text-muted mb-9">{{ __('ContactPage.faqIntro') }}</p>
        <div class="contact-faq">
            @foreach ($faqItems as $item)
                <x-accordion-item :title="$item['question']">
                    <p>{{ $item['answer'] }}</p>
                </x-accordion-item>
            @endforeach
        </div>
    </x-page-container>
</x-layouts.app>
