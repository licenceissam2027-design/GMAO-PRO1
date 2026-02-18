<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('gmao.auth.register') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="{{ asset('css/gmao.css') }}" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-card wide">
    <h3 class="mb-3 text-white">{{ __('gmao.auth.register') }}</h3><div class="mb-2 text-end"><a class="text-light me-2" href="{{ route('locale.switch','ar') }}">AR</a><a class="text-light me-2" href="{{ route('locale.switch','fr') }}">FR</a><a class="text-light" href="{{ route('locale.switch','en') }}">EN</a></div>
    <form method="POST" action="{{ route('register.post') }}">@csrf
        <div class="row g-2">
            <div class="col-md-6"><input class="form-control" name="name" placeholder="{{ __('gmao.auth.name') }}" required></div>
            <div class="col-md-6"><input class="form-control" name="email" type="email" placeholder="{{ __('gmao.auth.email') }}" required></div>
            <div class="col-md-6"><input class="form-control" name="phone" placeholder="{{ __('gmao.auth.phone') }}"></div>
            <div class="col-md-6"><input class="form-control" name="job_title" placeholder="{{ __('gmao.auth.job_title') }}"></div>
            <div class="col-md-6"><select class="form-select" name="sector"><option value="">{{ __('gmao.common.sector') }}</option>@foreach(['production','utilities','quality','it','logistics','hse','administration'] as $sector)<option value="{{ $sector }}">{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select></div>
            <div class="col-md-6"><input class="form-control" name="password" type="password" placeholder="{{ __('gmao.auth.password') }}" required></div>
            <div class="col-md-6"><input class="form-control" name="password_confirmation" type="password" placeholder="{{ __('gmao.auth.password_confirm') }}" required></div>
        </div>
        <button class="btn btn-warning w-100 mt-3">{{ __('gmao.auth.register') }}</button>
    </form>
    <a class="btn btn-link text-light mt-2" href="{{ route('login') }}">{{ __('gmao.auth.already_account') }}</a>
</div>
</body>
</html>
