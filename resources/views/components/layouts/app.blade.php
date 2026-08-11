<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('RootMetadata.title') }}</title>
    @isset($metaDescription)
        <meta name="description" content="{{ $metaDescription }}">
    @endisset

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
