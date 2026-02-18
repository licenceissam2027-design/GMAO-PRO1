@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell"><div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">{{ __('gmao.maintenance.new_task') }}</h5><a class="btn btn-sm btn-outline-secondary" href="{{ route('maintenance.tasks') }}">{{ __('gmao.common.open') }}</a></div>
<form method="POST" action="{{ route('maintenance.tasks.store') }}" class="row g-2">@csrf
<div class="col-md-4"><input class="form-control" name="title" placeholder="{{ __('gmao.common.title') }}" required></div>
<div class="col-md-2"><select class="form-select" name="sector"><option value="">{{ __('gmao.common.sector') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}">{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select></div>
<div class="col-md-2"><select class="form-select" name="type">@foreach(['corrective','preventive','predictive'] as $t)<option value="{{ $t }}">{{ __('gmao.enum.type.'.$t) }}</option>@endforeach</select></div>
<div class="col-md-2"><select class="form-select" name="status">@foreach(['pending','in_progress','completed','stopped'] as $s)<option value="{{ $s }}">{{ __('gmao.enum.status.'.$s) }}</option>@endforeach</select></div>
<div class="col-md-2"><input class="form-control" type="date" name="scheduled_for"></div>
<div class="col-md-6"><select class="form-select" name="maintenance_request_id"><option value="">{{ __('gmao.maintenance.link_request') }}</option>@foreach($requests as $r)<option value="{{ $r->id }}">#{{ $r->id }}</option>@endforeach</select></div>
<div class="col-md-6"><select class="form-select" name="technician_id"><option value="">{{ __('gmao.maintenance.tech_responsible') }}</option>@foreach($technicians as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select></div>
<div class="col-md-4"><input class="form-control" type="number" step="0.25" name="estimated_hours" placeholder="{{ __('gmao.maintenance.estimated_hours') }}"></div>
<div class="col-12"><textarea class="form-control" name="notes" placeholder="{{ __('gmao.common.notes') }}"></textarea></div>
<div class="col-12"><button class="btn btn-primary">{{ __('gmao.common.save') }}</button></div>
</form></div>
@endsection
