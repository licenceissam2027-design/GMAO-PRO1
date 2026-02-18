@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">{{ __('gmao.assets.expert_new') }}</h5><a class="btn btn-sm btn-outline-secondary" href="{{ route('assets.experts') }}">{{ __('gmao.common.open') }}</a></div>
<form method="POST" action="{{ route('assets.experts.store') }}" class="row g-2">@csrf
<div class="col-md-4"><input class="form-control" name="expert_name" placeholder="{{ __('gmao.common.name') }}" required></div><div class="col-md-4"><input class="form-control" name="company" placeholder="{{ __('gmao.common.company') }}"></div><div class="col-md-4"><input class="form-control" name="specialty" placeholder="{{ __('gmao.common.specialty') }}" required></div>
<div class="col-md-6"><input class="form-control" name="mission_title" placeholder="{{ __('gmao.common.title') }}" required></div><div class="col-md-2"><input class="form-control" type="date" name="start_date" required></div><div class="col-md-2"><input class="form-control" type="date" name="end_date"></div><div class="col-md-2"><select class="form-select" name="status">@foreach(['planned','active','closed'] as $s)<option value="{{ $s }}">{{ __('gmao.enum.status.'.$s) }}</option>@endforeach</select></div>
<div class="col-md-3"><input class="form-control" type="number" step="0.01" name="daily_rate" placeholder="{{ __('gmao.common.rate') }}"></div>
<div class="col-12"><textarea class="form-control" name="notes" placeholder="{{ __('gmao.common.notes') }}"></textarea></div>
<div class="col-12"><button class="btn btn-primary">{{ __('gmao.common.save') }}</button></div>
</form></div>
@endsection
