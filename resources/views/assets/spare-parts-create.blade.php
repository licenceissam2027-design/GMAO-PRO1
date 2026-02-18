@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">{{ __('gmao.assets.part_new') }}</h5><a class="btn btn-sm btn-outline-secondary" href="{{ route('assets.spare-parts') }}">{{ __('gmao.common.open') }}</a></div>
<form method="POST" action="{{ route('assets.spare-parts.store') }}" class="row g-2">@csrf
<div class="col-md-4"><input class="form-control" name="name" placeholder="{{ __('gmao.common.name') }}" required></div><div class="col-md-3"><input class="form-control" name="sku" placeholder="SKU" required></div><div class="col-md-3"><select class="form-select" name="sector"><option value="">{{ __('gmao.common.sector') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}">{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select></div><div class="col-md-2"><input class="form-control" name="category" placeholder="{{ __('gmao.common.category') }}"></div>
<div class="col-md-3"><input class="form-control" type="number" name="current_stock" placeholder="{{ __('gmao.common.stock') }}" required></div><div class="col-md-3"><input class="form-control" type="number" name="minimum_stock" placeholder="{{ __('gmao.common.min_stock') }}" required></div><div class="col-md-3"><input class="form-control" type="number" step="0.01" name="unit_price" placeholder="{{ __('gmao.common.price') }}"></div><div class="col-md-3"><input class="form-control" name="supplier" placeholder="{{ __('gmao.common.company') }}"></div>
<div class="col-md-6"><input class="form-control" name="shelf_location" placeholder="{{ __('gmao.common.location') }}"></div>
<div class="col-12"><button class="btn btn-primary">{{ __('gmao.common.save') }}</button></div>
</form></div>
@endsection
