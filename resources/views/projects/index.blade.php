@extends('layouts.app')
@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">{{ __('gmao.projects.list') }}</h5>
        <div class="d-flex align-items-center gap-2">
            @if(auth()->user()->isRole('super_admin','manager'))
                <a class="action-icon" href="{{ route('projects.create') }}"><i class="bi bi-plus-circle"></i><span>{{ __('gmao.common.add') }}</span></a>
            @endif
            <form method="GET" class="d-flex gap-1">
                <select class="form-select form-select-sm" name="sector"><option value="">{{ __('gmao.common.all_sectors') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}" @selected($selectedSector === $sector)>{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select>
                <button class="btn btn-sm btn-outline-secondary">{{ __('gmao.common.search') }}</button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>{{ __('gmao.projects.name') }}</th><th>{{ __('gmao.common.sector') }}</th><th>{{ __('gmao.projects.code') }}</th><th>{{ __('gmao.common.status') }}</th><th>{{ __('gmao.common.priority') }}</th><th>{{ __('gmao.projects.progress') }}</th><th>{{ __('gmao.projects.phases') }}</th><th>{{ __('gmao.common.actions') }}</th></tr></thead>
            <tbody>
            @forelse($projects as $project)
                <tr>
                    <td>{{ $project->name }}</td>
                    <td>{{ $project->sector ? __('gmao.enum.sector.'.$project->sector) : '-' }}</td>
                    <td>{{ $project->code ?: '-' }}</td>
                    <td>{{ __('gmao.enum.status.'.$project->status) }}</td>
                    <td>{{ __('gmao.enum.priority.'.$project->priority) }}</td>
                    <td>{{ $project->progress }}%</td>
                    <td>
                        @if($phasesEnabled)
                            <span class="badge text-bg-info">{{ $project->phases->count() }}</span>
                            <span class="small text-muted">
                                {{ $project->phases->where('status', 'completed')->count() }}/{{ max(1, $project->phases->count()) }}
                            </span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td>
                        <a class="btn btn-sm btn-outline-success" href="{{ route('assets.reports.create', ['context_type' => 'project', 'context_id' => $project->id]) }}"><i class="bi bi-file-earmark-plus"></i></a>
                        <a class="btn btn-sm btn-outline-info" href="{{ route('assets.reports', ['context_type' => 'project', 'context_id' => $project->id]) }}"><i class="bi bi-files"></i></a>
                        @can('update', $project)
                        <details>
                            <summary class="btn btn-sm btn-outline-primary">{{ __('gmao.common.edit') }}</summary>
                            <form method="POST" action="{{ route('projects.update', $project) }}" class="vstack gap-1 mt-2">@csrf @method('PATCH')
                                <input class="form-control form-control-sm" name="name" value="{{ $project->name }}" required>
                                <input class="form-control form-control-sm" name="code" value="{{ $project->code }}">
                                <select class="form-select form-select-sm" name="sector"><option value="">{{ __('gmao.common.sector') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}" @selected($project->sector === $sector)>{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select>
                                <select class="form-select form-select-sm" name="manager_id"><option value="">{{ __('gmao.projects.manager') }}</option>@foreach($managers as $m)<option value="{{ $m->id }}" @selected($project->manager_id === $m->id)>{{ $m->name }}</option>@endforeach</select>
                                <select class="form-select form-select-sm" name="priority">@foreach(['low','medium','high','critical'] as $p)<option value="{{ $p }}" @selected($project->priority === $p)>{{ __('gmao.enum.priority.'.$p) }}</option>@endforeach</select>
                                <select class="form-select form-select-sm" name="status">@foreach(['planned','in_progress','completed','delayed'] as $s)<option value="{{ $s }}" @selected($project->status === $s)>{{ __('gmao.enum.status.'.$s) }}</option>@endforeach</select>
                                <input class="form-control form-control-sm" type="number" min="0" max="100" name="progress" value="{{ $project->progress }}">
                                <input class="form-control form-control-sm" type="number" step="0.01" name="budget" value="{{ $project->budget }}">
                                <textarea class="form-control form-control-sm" name="description">{{ $project->description }}</textarea>
                                <button class="btn btn-sm btn-primary">{{ __('gmao.common.update') }}</button>
                            </form>
                        </details>
                        @endcan
                        @can('delete', $project)
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" class="mt-1" onsubmit="return confirm('{{ __('gmao.common.confirm_delete') }}')">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('gmao.common.delete') }}</button>
                        </form>
                        @endcan
                        @if($phasesEnabled)
                        @can('update', $project)
                        <details class="mt-1">
                            <summary class="btn btn-sm btn-outline-secondary">{{ __('gmao.projects.manage_phases') }}</summary>
                            <div class="mt-2 vstack gap-2">
                                @foreach($project->phases as $phase)
                                    <form method="POST" action="{{ route('projects.phases.update', $phase) }}" class="row g-1 border rounded p-2">@csrf @method('PATCH')
                                        <div class="col-md-3"><input class="form-control form-control-sm" name="title" value="{{ $phase->title }}" required></div>
                                        <div class="col-md-1"><input class="form-control form-control-sm" type="number" min="1" max="999" name="phase_order" value="{{ $phase->phase_order }}"></div>
                                        <div class="col-md-2"><select class="form-select form-select-sm" name="execution_mode">@foreach($phaseModes as $mode)<option value="{{ $mode }}" @selected($phase->execution_mode === $mode)>{{ __('gmao.projects.phase_mode_'.$mode) }}</option>@endforeach</select></div>
                                        <div class="col-md-2"><select class="form-select form-select-sm" name="status">@foreach($phaseStatuses as $status)<option value="{{ $status }}" @selected($phase->status === $status)>{{ __('gmao.enum.status.'.$status) }}</option>@endforeach</select></div>
                                        <div class="col-md-1"><input class="form-control form-control-sm" type="number" min="0" max="100" name="progress" value="{{ $phase->progress }}"></div>
                                        <div class="col-md-3"><select class="form-select form-select-sm" name="responsible_id"><option value="">{{ __('gmao.projects.responsible') }}</option>@foreach($phaseOwners as $owner)<option value="{{ $owner->id }}" @selected($phase->responsible_id === $owner->id)>{{ $owner->name }}</option>@endforeach</select></div>
                                        <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="planned_start_date" value="{{ optional($phase->planned_start_date)->format('Y-m-d') }}"></div>
                                        <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="planned_end_date" value="{{ optional($phase->planned_end_date)->format('Y-m-d') }}"></div>
                                        <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="actual_start_date" value="{{ optional($phase->actual_start_date)->format('Y-m-d') }}"></div>
                                        <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="actual_end_date" value="{{ optional($phase->actual_end_date)->format('Y-m-d') }}"></div>
                                        <div class="col-md-3">
                                            <select class="form-select form-select-sm" name="depends_on_phase_id">
                                                <option value="">{{ __('gmao.projects.depends_on_phase') }}</option>
                                                @foreach($project->phases as $dep)
                                                    @if($dep->id !== $phase->id)
                                                        <option value="{{ $dep->id }}" @selected($phase->depends_on_phase_id === $dep->id)>#{{ $dep->phase_order }} - {{ $dep->title }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-7"><textarea class="form-control form-control-sm" name="description" rows="1">{{ $phase->description }}</textarea></div>
                                        <div class="col-md-2 d-flex gap-1">
                                            <button class="btn btn-sm btn-primary flex-fill">{{ __('gmao.common.update') }}</button>
                                        </div>
                                    </form>
                                    <form method="POST" action="{{ route('projects.phases.destroy', $phase) }}" onsubmit="return confirm('{{ __('gmao.common.confirm_delete') }}')">@csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">{{ __('gmao.common.delete') }}</button>
                                    </form>
                                @endforeach

                                <form method="POST" action="{{ route('projects.phases.store', $project) }}" class="row g-1 border rounded p-2 bg-light">@csrf
                                    <div class="col-md-3"><input class="form-control form-control-sm" name="title" placeholder="{{ __('gmao.projects.phase_name') }}" required></div>
                                    <div class="col-md-1"><input class="form-control form-control-sm" type="number" min="1" max="999" name="phase_order" value="{{ $project->phases->count() + 1 }}" required></div>
                                    <div class="col-md-2"><select class="form-select form-select-sm" name="execution_mode">@foreach($phaseModes as $mode)<option value="{{ $mode }}">{{ __('gmao.projects.phase_mode_'.$mode) }}</option>@endforeach</select></div>
                                    <div class="col-md-2"><select class="form-select form-select-sm" name="status">@foreach($phaseStatuses as $status)<option value="{{ $status }}">{{ __('gmao.enum.status.'.$status) }}</option>@endforeach</select></div>
                                    <div class="col-md-1"><input class="form-control form-control-sm" type="number" min="0" max="100" name="progress" value="0" required></div>
                                    <div class="col-md-3"><select class="form-select form-select-sm" name="responsible_id"><option value="">{{ __('gmao.projects.responsible') }}</option>@foreach($phaseOwners as $owner)<option value="{{ $owner->id }}">{{ $owner->name }}</option>@endforeach</select></div>
                                    <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="planned_start_date"></div>
                                    <div class="col-md-3"><input class="form-control form-control-sm" type="date" name="planned_end_date"></div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" name="depends_on_phase_id">
                                            <option value="">{{ __('gmao.projects.depends_on_phase') }}</option>
                                            @foreach($project->phases as $dep)
                                                <option value="{{ $dep->id }}">#{{ $dep->phase_order }} - {{ $dep->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3"><button class="btn btn-sm btn-success w-100">{{ __('gmao.projects.add_phase') }}</button></div>
                                    <div class="col-12"><textarea class="form-control form-control-sm" name="description" rows="1" placeholder="{{ __('gmao.projects.phase_description') }}"></textarea></div>
                                </form>
                            </div>
                        </details>
                        @endcan
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center">{{ __('gmao.common.none') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if(!$phasesEnabled)
        <div class="alert alert-warning mt-3 mb-0">{{ __('gmao.msg.project_phases_unavailable') }}</div>
    @endif
    {{ $projects->links() }}
</div>
@endsection
