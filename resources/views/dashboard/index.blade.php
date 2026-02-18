@extends('layouts.app')
@section('content')
<div class="dashboard-kpi-grid mb-3">
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.open_requests') }}</div><div class="kpi-value">{{ $kpiCards['open_requests'] }}</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.critical_requests') }}</div><div class="kpi-value text-danger">{{ $kpiCards['critical_requests'] }}</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.completed_tasks_month') }}</div><div class="kpi-value">{{ $kpiCards['completed_tasks_month'] }}</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.low_stock_parts') }}</div><div class="kpi-value text-warning">{{ $kpiCards['low_stock_parts'] }}</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.asset_pool') }}</div><div class="kpi-value">{{ $kpiCards['asset_pool'] }}</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.availability_rate') }}</div><div class="kpi-value">{{ $kpiCards['availability_rate'] }}%</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.mttr_hours') }}</div><div class="kpi-value">{{ $kpiCards['mttr_hours'] }}</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.mtbf_hours') }}</div><div class="kpi-value">{{ $kpiCards['mtbf_hours'] }}</div></div>
    <div class="kpi-card"><div class="kpi-label">{{ __('gmao.dashboard.recurrent_rate') }}</div><div class="kpi-value">{{ $kpiCards['recurrent_rate'] }}%</div></div>
</div>

<div class="row g-3">
    <div class="col-xl-4"><div class="card p-3 dashboard-chart-card"><h6>{{ __('gmao.dashboard.project_status') }}</h6><canvas id="projectStatusChart" class="dashboard-chart-pie" height="190" data-values='@json($projectStatus)'></canvas></div></div>
    <div class="col-xl-4"><div class="card p-3 dashboard-chart-card"><h6>{{ __('gmao.dashboard.request_status') }}</h6><canvas id="requestStatusChart" class="dashboard-chart-pie" height="190" data-values='@json($requestStatus)'></canvas></div></div>
    <div class="col-xl-4"><div class="card p-3 dashboard-chart-card"><h6>{{ __('gmao.dashboard.task_types') }}</h6><canvas id="tasksTypeChart" class="dashboard-chart-pie" height="190" data-values='@json($tasksByType)'></canvas></div></div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card p-3 dashboard-chart-card">
            <h6>{{ __('gmao.dashboard.sector_backlog') }}</h6>
            <canvas id="sectorBacklogChart" class="dashboard-chart-bar" height="150" data-values='@json($sectorBacklog)'></canvas>
        </div>
    </div>
</div>
@endsection
