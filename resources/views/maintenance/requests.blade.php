@extends('layouts.app')
@section('content')
<div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0">{{ __('gmao.maintenance.requests_list') }}</h5>
        <div class="d-flex align-items-center gap-2">
            <a class="action-icon" href="{{ route('maintenance.requests.create') }}"><i class="bi bi-tools"></i><span>{{ __('gmao.common.add') }}</span></a>
            <form method="GET" class="d-flex gap-1">
                <select class="form-select form-select-sm" name="sector"><option value="">{{ __('gmao.common.all_sectors') }}</option>@foreach($sectors as $sector)<option value="{{ $sector }}" @selected($selectedSector === $sector)>{{ __('gmao.enum.sector.'.$sector) }}</option>@endforeach</select>
                <button class="btn btn-sm btn-outline-secondary">{{ __('gmao.common.search') }}</button>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle">
            <thead><tr><th>{{ __('gmao.maintenance.ticket_code') }}</th><th>{{ __('gmao.common.sector') }}</th><th>{{ __('gmao.maintenance.domain') }}</th><th>{{ __('gmao.maintenance.failure_mode') }}</th><th>{{ __('gmao.common.status') }}</th><th>{{ __('gmao.maintenance.recurrence') }}</th><th>{{ __('gmao.common.actions') }}</th></tr></thead>
            <tbody>
            @forelse($requests as $row)
                <tr>
                    <td>{{ $row->request_code ?? ('#'.$row->id) }}</td>
                    <td>{{ $row->sector ? __('gmao.enum.sector.'.$row->sector) : '-' }}</td>
                    <td>{{ $row->maintenance_domain ? ($domainLabels[$row->maintenance_domain] ?? $row->maintenance_domain) : '-' }}</td>
                    <td>{{ $row->failure_mode ? ($failureModeLabels[$row->failure_mode] ?? $row->failure_mode) : '-' }}</td>
                    <td>{{ __('gmao.enum.status.'.$row->status) }}</td>
                    <td>@if($row->is_recurrent)<span class="badge text-bg-warning">{{ __('gmao.maintenance.recurrent_badge', ['count' => $row->recurrence_count]) }}</span>@else<span class="badge text-bg-success">{{ __('gmao.maintenance.non_recurrent') }}</span>@endif</td>
                    <td>
                        <a class="btn btn-sm btn-outline-success" href="{{ route('assets.reports.create', ['context_type' => 'maintenance_request', 'context_id' => $row->id]) }}"><i class="bi bi-file-earmark-plus"></i></a>
                        <a class="btn btn-sm btn-outline-info" href="{{ route('assets.reports', ['context_type' => 'maintenance_request', 'context_id' => $row->id]) }}"><i class="bi bi-files"></i></a>
                        <a class="btn btn-sm btn-outline-info" href="{{ route('maintenance.requests.show', $row) }}"><i class="bi bi-eye"></i></a>
                        @if(auth()->user()->isRole('super_admin','manager','technician'))
                        <form method="POST" action="{{ route('maintenance.requests.status', $row) }}" class="mt-1">@csrf @method('PATCH')
                            <div class="d-flex gap-1"><select class="form-select form-select-sm" name="status">@foreach(['pending','in_progress','completed','stopped'] as $s)<option value="{{ $s }}" @selected($row->status === $s)>{{ __('gmao.enum.status.'.$s) }}</option>@endforeach</select><button class="btn btn-sm btn-outline-primary">{{ __('gmao.common.update') }}</button></div>
                        </form>
                        @endif
                        @can('delete', $row)
                        <form method="POST" action="{{ route('maintenance.requests.destroy', $row) }}" class="mt-1" onsubmit="return confirm('{{ __('gmao.common.confirm_delete') }}')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">{{ __('gmao.common.delete') }}</button></form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">{{ __('gmao.common.none') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $requests->links() }}
</div>
@endsection
