<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('gmao.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height:100vh;">
    <main class="container">
        <div class="card shadow-sm border-0 mx-auto" style="max-width: 520px;">
            <div class="card-body p-4 text-center">
                <h1 class="h4 mb-3">{{ __('gmao.app_name') }}</h1>
                <p class="text-muted mb-0">Unexpected error. Please try again.</p>
            </div>
        </div>
    </main>
</body>
</html>

