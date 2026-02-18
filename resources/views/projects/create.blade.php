@extends('layouts.app')
@section('content')
<div class="card p-3 form-shell">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('gmao.projects.add') }}</h5>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('projects.index') }}">{{ __('gmao.common.open') }}</a>
    </div>
    <form method="POST" action="{{ route('projects.store') }}" class="row g-2">@csrf
        <div class="col-md-6"><input class="form-control" name="name" placeholder="{{ __('gmao.projects.name') }}" required></div>
        <div class="col-md-3"><input class="form-control" name="code" placeholder="{{ __('gmao.projects.code') }}"></div>
        <div class="col-md-3"><select class="form-select" name="sector"><option value="">{{ __('gmao.common.sector') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}">{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select></div>
        <div class="col-md-4"><select class="form-select" name="manager_id"><option value="">{{ __('gmao.projects.manager') }}</option>@foreach($managers as $m)<option value="{{ $m->id }}">{{ $m->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><select class="form-select" name="priority">@foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}">{{ __('gmao.enum.priority.'.$p) }}</option>@endforeach</select></div>
        <div class="col-md-2"><select class="form-select" name="status">@foreach(['planned','in_progress','completed','delayed'] as $s)<option value="{{ $s }}">{{ __('gmao.enum.status.'.$s) }}</option>@endforeach</select></div>
        <div class="col-md-2"><input class="form-control" type="number" min="0" max="100" name="progress" placeholder="{{ __('gmao.projects.progress') }}"></div>
        <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="budget" placeholder="{{ __('gmao.projects.budget') }}"></div>
        <div class="col-md-2"><input class="form-control" type="date" name="start_date"></div>
        <div class="col-md-2"><input class="form-control" type="date" name="end_date"></div>
        <div class="col-12"><textarea class="form-control" name="description" placeholder="{{ __('gmao.projects.description') }}"></textarea></div>

        @if($phasesEnabled)
            <div class="col-12 mt-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('gmao.projects.phases') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addProjectPhaseRow">{{ __('gmao.projects.add_phase') }}</button>
                </div>
            </div>
            <div class="col-12" id="projectPhasesContainer">
                <div class="row g-2 border rounded-3 p-2 project-phase-row" data-index="0">
                    <div class="col-md-3"><input class="form-control" name="phases[0][title]" placeholder="{{ __('gmao.projects.phase_name') }}"></div>
                    <div class="col-md-2"><input class="form-control" type="number" min="1" max="999" name="phases[0][phase_order]" value="1" placeholder="{{ __('gmao.projects.phase_order') }}"></div>
                    <div class="col-md-2"><select class="form-select" name="phases[0][execution_mode]">@foreach($phaseModes as $mode)<option value="{{ $mode }}">{{ __('gmao.projects.phase_mode_'.$mode) }}</option>@endforeach</select></div>
                    <div class="col-md-2"><select class="form-select" name="phases[0][status]">@foreach($phaseStatuses as $status)<option value="{{ $status }}">{{ __('gmao.enum.status.'.$status) }}</option>@endforeach</select></div>
                    <div class="col-md-1"><input class="form-control" type="number" min="0" max="100" name="phases[0][progress]" value="0"></div>
                    <div class="col-md-2"><select class="form-select" name="phases[0][responsible_id]"><option value="">{{ __('gmao.projects.responsible') }}</option>@foreach($phaseOwners as $owner)<option value="{{ $owner->id }}">{{ $owner->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><input class="form-control" type="date" name="phases[0][planned_start_date]"></div>
                    <div class="col-md-3"><input class="form-control" type="date" name="phases[0][planned_end_date]"></div>
                    <div class="col-md-5"><textarea class="form-control" name="phases[0][description]" rows="1" placeholder="{{ __('gmao.projects.phase_description') }}"></textarea></div>
                    <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-phase-row">&times;</button></div>
                </div>
            </div>
        @else
            <div class="col-12">
                <div class="alert alert-warning mb-0">{{ __('gmao.msg.project_phases_unavailable') }}</div>
            </div>
        @endif
        <div class="col-12"><button class="btn btn-primary">{{ __('gmao.common.save') }}</button></div>
    </form>
</div>
@endsection

@push('scripts')
@if($phasesEnabled)
<script>
(() => {
    const container = document.getElementById('projectPhasesContainer');
    const addBtn = document.getElementById('addProjectPhaseRow');
    if (!container || !addBtn) return;

    function bindRemoveButtons(scope) {
        scope.querySelectorAll('.remove-phase-row').forEach((btn) => {
            btn.onclick = () => {
                const rows = container.querySelectorAll('.project-phase-row');
                if (rows.length === 1) return;
                btn.closest('.project-phase-row')?.remove();
            };
        });
    }

    function nextIndex() {
        return container.querySelectorAll('.project-phase-row').length;
    }

    addBtn.addEventListener('click', () => {
        const idx = nextIndex();
        const row = document.createElement('div');
        row.className = 'row g-2 border rounded-3 p-2 project-phase-row mt-2';
        row.dataset.index = String(idx);
        row.innerHTML = `
            <div class="col-md-3"><input class="form-control" name="phases[${idx}][title]" placeholder="{{ __('gmao.projects.phase_name') }}"></div>
            <div class="col-md-2"><input class="form-control" type="number" min="1" max="999" name="phases[${idx}][phase_order]" value="${idx + 1}" placeholder="{{ __('gmao.projects.phase_order') }}"></div>
            <div class="col-md-2"><select class="form-select" name="phases[${idx}][execution_mode]">@foreach($phaseModes as $mode)<option value="{{ $mode }}">{{ __('gmao.projects.phase_mode_'.$mode) }}</option>@endforeach</select></div>
            <div class="col-md-2"><select class="form-select" name="phases[${idx}][status]">@foreach($phaseStatuses as $status)<option value="{{ $status }}">{{ __('gmao.enum.status.'.$status) }}</option>@endforeach</select></div>
            <div class="col-md-1"><input class="form-control" type="number" min="0" max="100" name="phases[${idx}][progress]" value="0"></div>
            <div class="col-md-2"><select class="form-select" name="phases[${idx}][responsible_id]"><option value="">{{ __('gmao.projects.responsible') }}</option>@foreach($phaseOwners as $owner)<option value="{{ $owner->id }}">{{ $owner->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><input class="form-control" type="date" name="phases[${idx}][planned_start_date]"></div>
            <div class="col-md-3"><input class="form-control" type="date" name="phases[${idx}][planned_end_date]"></div>
            <div class="col-md-5"><textarea class="form-control" name="phases[${idx}][description]" rows="1" placeholder="{{ __('gmao.projects.phase_description') }}"></textarea></div>
            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-phase-row">&times;</button></div>
        `;
        container.appendChild(row);
        bindRemoveButtons(row);
    });

    bindRemoveButtons(container);
})();
</script>
@endif
@endpush
