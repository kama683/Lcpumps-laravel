{{--
    Stage 4 routing smoke-test placeholder — every public route renders this
    until Stage 5 replaces each controller action's view() call with the
    real ported template. Confirms locale/route/param resolution end-to-end.
--}}
<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><title>{{ $routeName }} [{{ app()->getLocale() }}]</title></head>
<body style="font-family: sans-serif; padding: 2rem;">
    <p>route: <strong>{{ $routeName }}</strong></p>
    <p>locale: <strong>{{ app()->getLocale() }}</strong></p>
    @if (!empty($params))
        <p>params: <strong>{{ json_encode($params, JSON_UNESCAPED_UNICODE) }}</strong></p>
    @endif
    <p>self (route()): <a href="{{ route($routeName, $params) }}">{{ route($routeName, $params) }}</a></p>
</body>
</html>
