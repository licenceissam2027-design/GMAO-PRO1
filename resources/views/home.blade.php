@extends('layouts.app')

@section('content')
<section class="hero-grid">
    <div class="hero-panel">
        <div class="hero-kicker">{{ __('gmao.app_name') }}</div>
        <h1>{{ __('gmao.home.title') }}</h1>
        <p>{{ __('gmao.home.subtitle') }}</p>
        <div class="stat-cards">
            <div><strong>{{ $stats['projects_total'] }}</strong><span>{{ __('gmao.home.projects_total') }}</span></div>
            <div><strong>{{ $stats['projects_in_progress'] }}</strong><span>{{ __('gmao.home.projects_progress') }}</span></div>
            <div><strong>{{ $stats['requests_pending'] }}</strong><span>{{ __('gmao.home.pending_requests') }}</span></div>
            <div><strong>{{ $stats['low_stock_parts'] }}</strong><span>{{ __('gmao.home.low_stock') }}</span></div>
        </div>
        <div class="hero-actions mt-3">
            @can('create', App\Models\MaintenanceRequest::class)
            <a class="action-icon" href="{{ route('maintenance.requests.create') }}"><i class="bi bi-tools"></i><span>{{ __('gmao.maintenance.new_request') }}</span></a>
            @endcan
            @can('create', App\Models\PreventivePlan::class)
            <a class="action-icon" href="{{ route('maintenance.plans.create') }}"><i class="bi bi-shield-check"></i><span>{{ __('gmao.maintenance.new_plan') }}</span></a>
            @endcan
            @can('create', App\Models\Project::class)
            <a class="action-icon" href="{{ route('projects.create') }}"><i class="bi bi-kanban"></i><span>{{ __('gmao.projects.add') }}</span></a>
            @endcan
        </div>
    </div>
    <div class="clock-weather">
        <div class="clock-shell">
            <canvas id="analogClock" width="240" height="240"></canvas>
            <div class="digital-clock" id="digitalClock">--:--:--</div>
            <div class="clock-date" id="clockDate">--</div>
        </div>
        <div class="weather-box" id="weatherBox">{{ __('gmao.home.weather_loading') }}</div>
    </div>
</section>

<section class="home-media-grid mt-4">
    <article class="company-card card p-3">
        <h5 class="mb-2">{{ __('gmao.home.company_spotlight') }}</h5>
        <p class="text-muted mb-3">{{ __('gmao.home.company_spotlight_sub') }}</p>
        <img src="{{ asset('images/home/PROJECT_MEDIA/company-photo.jpg') }}" alt="Company" class="img-fluid rounded-3 company-photo">
    </article>
    <article class="card p-3 media-tile">
        <img src="{{ asset('images/home/PROJECT_MEDIA/maintenance.jpg') }}" alt="Maintenance">
        <h6>{{ __('gmao.nav.requests') }}</h6>
    </article>
    <article class="card p-3 media-tile">
        <img src="{{ asset('images/home/PROJECT_MEDIA/projects.jpg') }}" alt="Projects">
        <h6>{{ __('gmao.nav.projects') }}</h6>
    </article>
    <article class="card p-3 media-tile">
        <img src="{{ asset('images/home/PROJECT_MEDIA/logistics.jpg') }}" alt="Logistics">
        <h6>{{ __('gmao.nav.logistics') }}</h6>
    </article>
    <article class="card p-3 media-tile">
        <img src="{{ asset('images/home/PROJECT_MEDIA/spareparts.jpg') }}" alt="Spare Parts">
        <h6>{{ __('gmao.nav.parts') }}</h6>
    </article>
</section>

<section class="card mt-4 p-3">
    <h5>{{ __('gmao.home.latest_requests') }}</h5>
    <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>#</th><th>{{ __('gmao.common.status') }}</th><th>{{ __('gmao.common.priority') }}</th><th>{{ __('gmao.common.date') }}</th></tr></thead><tbody>
    @forelse($recentRequests as $req)
        <tr><td>{{ $req->id }}</td><td>{{ __('gmao.enum.issue.'.$req->issue_category) }}</td><td>{{ __('gmao.enum.priority.'.$req->severity) }}</td><td>{{ optional($req->requested_at)->format('Y-m-d H:i') }}</td></tr>
    @empty
        <tr><td colspan="4" class="text-center">{{ __('gmao.common.none') }}</td></tr>
    @endforelse
    </tbody></table></div>
</section>
@endsection


