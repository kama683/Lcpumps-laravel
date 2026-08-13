<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('RootMetadata.title') }}</title>
    @isset($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endisset

    {{-- hreflang: one <link> per locale (ru/kk/en/zh) plus x-default (→ ru),
         resolved from the route actually matched for this request so every
         controller gets it for free. Absolute https://www.lcpumps.kz URLs,
         reciprocal by construction — see SeoMetadataService::hreflangUrls(). --}}
    @foreach (app(\App\Services\SeoMetadataService::class)->hreflangForCurrentRequest() as $hreflangLocale => $hreflangHref)
        <link rel="alternate" hreflang="{{ $hreflangLocale }}" href="{{ $hreflangHref }}">
    @endforeach

    {{-- Progressive-enhancement gate: see resources/css/app.css for the
         html.js-scoped rules this enables (scroll reveal, image fade-in). --}}
    <script>document.documentElement.classList.add('js')</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ Vite::fonts() }}
</head>
<body>
    @include('partials.header')
    <main>
        {{ $slot }}
    </main>
    @include('partials.footer')
</body>
</html>
