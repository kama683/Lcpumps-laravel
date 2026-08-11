<a
    href="{{ route('contact.index') }}"
    class="tablet:hidden fixed bottom-5 right-5 z-40 inline-flex items-center gap-2 rounded-pill bg-primary pl-4 pr-5 py-3.5 text-sm font-bold text-white shadow-btn no-underline transition-transform duration-200 hover:-translate-y-0.5 hover:text-white active:translate-y-0 active:scale-95"
>
    <x-icon name="message-circle" stroke-width="2.25" class="size-4.5 shrink-0" />
    {{ __('Nav.contactCta') }}
</a>
