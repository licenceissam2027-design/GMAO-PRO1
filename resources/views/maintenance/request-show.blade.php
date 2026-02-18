@extends('layouts.app')
@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-1">{{ $requestItem->request_code ?? ('#'.$requestItem->id) }}</h5>
                    <div class="text-muted">{{ __('gmao.maintenance.request_details') }}</div>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <a class="btn btn-sm btn-outline-success" href="{{ route('assets.reports.create', ['context_type' => 'maintenance_request', 'context_id' => $requestItem->id]) }}"><i class="bi bi-file-earmark-plus"></i></a>
                    <a class="btn btn-sm btn-outline-info" href="{{ route('assets.reports', ['context_type' => 'maintenance_request', 'context_id' => $requestItem->id]) }}"><i class="bi bi-files"></i></a>
                    <span class="badge text-bg-primary">{{ __('gmao.enum.status.'.$requestItem->status) }}</span>
                </div>
            </div>
            <hr>
            <div class="row g-2 small">
                <div class="col-md-6"><strong>{{ __('gmao.common.sector') }}:</strong> {{ $requestItem->sector ? __('gmao.enum.sector.'.$requestItem->sector) : '-' }}</div>
                <div class="col-md-6"><strong>{{ __('gmao.common.priority') }}:</strong> {{ __('gmao.enum.priority.'.$requestItem->severity) }}</div>
                <div class="col-md-6"><strong>{{ __('gmao.maintenance.domain') }}:</strong> {{ $requestItem->maintenance_domain ? ($domainLabels[$requestItem->maintenance_domain] ?? $requestItem->maintenance_domain) : '-' }}</div>
                <div class="col-md-6"><strong>{{ __('gmao.maintenance.failure_mode') }}:</strong> {{ $requestItem->failure_mode ? ($failureModeLabels[$requestItem->failure_mode] ?? $requestItem->failure_mode) : '-' }}</div>
                <div class="col-md-6">
                    <strong>{{ __('gmao.common.type') }}:</strong> {{ __('gmao.enum.type.'.$requestItem->asset_type) }}
                </div>
                <div class="col-md-6">
                    <strong>{{ __('gmao.maintenance.machine') }}:</strong>
                    {{ $requestItem->machine?->name ?? $requestItem->technicalAsset?->name ?? $requestItem->logisticAsset?->name ?? '-' }}
                </div>
                <div class="col-md-6"><strong>{{ __('gmao.common.reference') }}:</strong> {{ $requestItem->asset_reference ?: '-' }}</div>
                <div class="col-md-6"><strong>{{ __('gmao.maintenance.occurrence_at') }}:</strong> {{ optional($requestItem->occurrence_at)->format('Y-m-d H:i') }}</div>
                <div class="col-md-6"><strong>{{ __('gmao.maintenance.downtime_minutes') }}:</strong> {{ $requestItem->downtime_minutes ?? '-' }}</div>
                <div class="col-md-6"><strong>{{ __('gmao.maintenance.requester') }}:</strong> {{ $requestItem->requester?->name ?? '-' }}</div>
                <div class="col-md-6"><strong>{{ __('gmao.maintenance.assignee') }}:</strong> {{ $requestItem->assignee?->name ?? '-' }}</div>
                <div class="col-12"><strong>{{ __('gmao.maintenance.request_desc') }}:</strong><div class="mt-1">{{ $requestItem->description }}</div></div>
            </div>
            <hr>
            @if($requestItem->is_recurrent)
                <div class="alert alert-warning mb-0">{{ __('gmao.maintenance.recurrent_badge', ['count' => $requestItem->recurrence_count]) }}</div>
            @else
                <div class="alert alert-success mb-0">{{ __('gmao.maintenance.non_recurrent') }}</div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card p-3">
            <h6>{{ __('gmao.maintenance.similar_history') }}</h6>
            <div class="vstack gap-2">
                @forelse($similarRequests as $item)
                    <a class="text-decoration-none border rounded p-2" href="{{ route('maintenance.requests.show', $item) }}">
                        <div class="small fw-semibold">{{ $item->request_code ?? ('#'.$item->id) }}</div>
                        <div class="small text-muted">{{ __('gmao.enum.status.'.$item->status) }} - {{ optional($item->created_at)->format('Y-m-d') }}</div>
                    </a>
                @empty
                    <div class="text-muted small">{{ __('gmao.common.none') }}</div>
                @endforelse
            </div>
        </div>
        <div class="card p-3 mt-3">
            <h6>{{ __('gmao.assets.reports_list') }}</h6>
            <div class="vstack gap-2">
                @forelse($linkedReports as $report)
                    <div class="border rounded p-2 d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-semibold">{{ $report->title }}</div>
                            <div class="small text-muted">{{ optional($report->report_date)->format('Y-m-d') }} - {{ __('gmao.enum.type.'.$report->format) }}</div>
                        </div>
                        @if($report->file_path)
                            <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ asset('storage/'.$report->file_path) }}">{{ __('gmao.common.open') }}</a>
                        @endif
                    </div>
                @empty
                    <div class="text-muted small">{{ __('gmao.common.none') }}</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
