{{--
    Bare, no-chrome 404 — for paths that matched no route at all (locale was
    never resolved). Mirrors app/global-not-found.tsx in the source project,
    which also renders outside the normal <html>/<body> layout tree. Falls
    back to the app's default locale, same as the source's fallback when no
    locale header is present.
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
        <p><a href="{{ url('/') }}">{{ __('NotFound.homeButton') }}</a></p>
    </main>
</body>
</html>
