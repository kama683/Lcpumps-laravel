{{--
    In-layout 404 — for a matched route whose controller calls abort(404) on
    a bad slug (product/project/about-us). Mirrors app/[locale]/not-found.tsx
    in the source project, which renders inside the normal Header/Footer
    layout tree. Standalone for now; Stage 5 wires this into the real shared
    layout once Header/Footer are ported.
--}}
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('PageMetadata.notFound.title') }}</title>
    <meta name="robots" content="noindex">
</head>
<body>
    <main style="max-width: 640px; margin: 4rem auto; padding: 0 1.5rem; font-family: sans-serif; text-align: center;">
        <p style="text-transform: uppercase; letter-spacing: .1em; font-size: .8rem; color: #b91c1c;">{{ __('NotFound.eyebrow') }}</p>
        <h1>{{ __('NotFound.title') }}</h1>
        <p>{{ __('NotFound.description') }}</p>
        <p>
            <a href="{{ route('home') }}">{{ __('NotFound.homeButton') }}</a>
            &middot;
            <a href="{{ route('products.index') }}">{{ __('NotFound.catalogButton') }}</a>
            &middot;
            <a href="{{ route('contact.index') }}">{{ __('NotFound.contactLink') }}</a>
        </p>
    </main>
</body>
</html>
