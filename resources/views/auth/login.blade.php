<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('gmao.auth.login') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="{{ asset('css/gmao.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-card">
    <h3 class="mb-3 text-white">{{ __('gmao.app_name') }}</h3><div class="mb-2 text-end"><a class="text-light me-2" href="{{ route('locale.switch','ar') }}">AR</a><a class="text-light me-2" href="{{ route('locale.switch','fr') }}">FR</a><a class="text-light" href="{{ route('locale.switch','en') }}">EN</a></div>
    @if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ route('login.post') }}">@csrf
        <div class="mb-3"><input class="form-control" type="email" name="email" placeholder="{{ __('gmao.auth.email') }}" required></div>
        <div class="mb-3"><input class="form-control" type="password" name="password" placeholder="{{ __('gmao.auth.password') }}" required></div>
        <button class="btn btn-warning w-100">{{ __('gmao.auth.login') }}</button>
    </form>
</div>
</body>
</html>



