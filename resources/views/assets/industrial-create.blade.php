@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">{{ __('gmao.assets.industrial_new') }}</h5><a class="btn btn-sm btn-outline-secondary" href="{{ route('assets.industrial') }}">{{ __('gmao.common.open') }}</a></div>
<form method="POST" action="{{ route('assets.industrial.store') }}" class="row g-2">@csrf
<div class="col-md-4"><input class="form-control" name="name" placeholder="{{ __('gmao.common.name') }}" required></div><div class="col-md-3"><input class="form-control" name="code" placeholder="{{ __('gmao.common.code') }}" required></div><div class="col-md-3"><select class="form-select" name="sector"><option value="">{{ __('gmao.common.sector') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}">{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select></div><div class="col-md-2"><select class="form-select" name="status">@foreach(['running','stopped','maintenance'] as $s)<option value="{{ $s }}">{{ __('gmao.enum.status.'.$s) }}</option>@endforeach</select></div>
<div class="col-md-3"><input class="form-control" name="manufacturer" placeholder="{{ __('gmao.common.manufacturer') }}"></div><div class="col-md-3"><input class="form-control" name="model" placeholder="{{ __('gmao.common.model') }}"></div><div class="col-md-3"><input class="form-control" name="serial_number" placeholder="{{ __('gmao.common.serial') }}"></div><div class="col-md-3"><input class="form-control" name="location" placeholder="{{ __('gmao.common.location') }}"></div>
<div class="col-md-3"><select class="form-select" name="criticality">@foreach(['low','medium','high'] as $p)<option value="{{ $p }}">{{ __('gmao.enum.priority.'.$p) }}</option>@endforeach</select></div>
<div class="col-12"><button class="btn btn-primary">{{ __('gmao.common.save') }}</button></div>
</form></div>
@endsection
