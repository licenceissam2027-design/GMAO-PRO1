<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('gmao.app_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ app()->getLocale() === 'ar' ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/gmao.css') }}" rel="stylesheet">
</head>
<body>
<div class="gmao-shell">
    <aside class="gmao-sidebar">
        <div class="brand-block">
            <h2>{{ __('gmao.app_name') }}</h2>
            <small>{{ __('gmao.home.title') }}</small>
        </div>
        <nav class="nav flex-column gap-1">
            <a class="nav-link" href="{{ route('home') }}"><i class="bi bi-house"></i> {{ __('gmao.nav.home') }}</a>
            <a class="nav-link" href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> {{ __('gmao.nav.dashboard') }}</a>
            <a class="nav-link" href="{{ route('projects.index') }}"><i class="bi bi-kanban"></i> {{ __('gmao.nav.projects') }}</a>
            <a class="nav-link" href="{{ route('maintenance.plans') }}"><i class="bi bi-shield-check"></i> {{ __('gmao.nav.plans') }}</a>
            <a class="nav-link" href="{{ route('maintenance.rounds') }}"><i class="bi bi-clipboard2-pulse"></i> {{ __('gmao.nav.rounds') }}</a>
            <a class="nav-link" href="{{ route('maintenance.requests') }}"><i class="bi bi-tools"></i> {{ __('gmao.nav.requests') }}</a>
            <a class="nav-link" href="{{ route('assets.industrial') }}"><i class="bi bi-gear-wide-connected"></i> {{ __('gmao.nav.industrial') }}</a>
            <a class="nav-link" href="{{ route('assets.technical') }}"><i class="bi bi-pc-display"></i> {{ __('gmao.nav.technical') }}</a>
            <a class="nav-link" href="{{ route('assets.spare-parts') }}"><i class="bi bi-box-seam"></i> {{ __('gmao.nav.parts') }}</a>
            @can('viewAny', App\Models\ExpertMission::class)
            <a class="nav-link" href="{{ route('assets.experts') }}"><i class="bi bi-person-workspace"></i> {{ __('gmao.nav.experts') }}</a>
            @endcan
            <a class="nav-link" href="{{ route('assets.reports') }}"><i class="bi bi-file-earmark-text"></i> {{ __('gmao.nav.reports') }}</a>
            <a class="nav-link" href="{{ route('assets.logistics') }}"><i class="bi bi-truck"></i> {{ __('gmao.nav.logistics') }}</a>
            @can('viewAny', App\Models\User::class)
            <a class="nav-link" href="{{ route('team.index') }}"><i class="bi bi-people"></i> {{ __('gmao.nav.team') }}</a>
            @endcan
        </nav>
    </aside>

    <main class="gmao-main">
        <header class="topbar">
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <span class="badge text-bg-light">{{ __('gmao.enum.role.'.auth()->user()->role) }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">{{ __('gmao.common.language') }}</button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('locale.switch', 'ar') }}">{{ __('gmao.lang.ar') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('locale.switch', 'fr') }}">{{ __('gmao.lang.fr') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('locale.switch', 'en') }}">{{ __('gmao.lang.en') }}</a></li>
                    </ul>
                </div>
                <a class="notif" href="#" data-bs-toggle="modal" data-bs-target="#notificationsModal"><i class="bi bi-bell"></i><span>{{ auth()->user()->unreadNotifications()->count() }}</span></a>
                <form action="{{ route('logout') }}" method="POST">@csrf <button class="btn btn-sm btn-outline-light">{{ __('gmao.auth.logout') }}</button></form>
            </div>
        </header>

        <section class="unified-strip mb-3">
            <div class="unified-kpi">
                <div class="mini-kpi"><span>{{ __('gmao.dashboard.open_requests') }}</span><strong>{{ $unifiedKpi['open_requests'] ?? 0 }}</strong></div>
                <div class="mini-kpi"><span>{{ __('gmao.dashboard.critical_requests') }}</span><strong>{{ $unifiedKpi['critical_requests'] ?? 0 }}</strong></div>
                <div class="mini-kpi"><span>{{ __('gmao.dashboard.low_stock_parts') }}</span><strong>{{ $unifiedKpi['low_stock_parts'] ?? 0 }}</strong></div>
                <div class="mini-kpi"><span>{{ __('gmao.dashboard.availability_rate') }}</span><strong>{{ $unifiedKpi['availability_rate'] ?? 0 }}%</strong></div>
            </div>
            <div class="unified-actions">
                @can('create', App\Models\MaintenanceRequest::class)
                    <a class="action-icon" href="{{ route('maintenance.requests.create') }}"><i class="bi bi-tools"></i><span>{{ __('gmao.maintenance.new_request') }}</span></a>
                @endcan
                @can('create', App\Models\Project::class)
                    <a class="action-icon" href="{{ route('projects.create') }}"><i class="bi bi-kanban"></i><span>{{ __('gmao.projects.add') }}</span></a>
                @endcan
                @can('create', App\Models\IndustrialMachine::class)
                    <a class="action-icon" href="{{ route('assets.industrial.create') }}"><i class="bi bi-gear-wide-connected"></i><span>{{ __('gmao.assets.industrial_new') }}</span></a>
                @endcan
                @can('create', App\Models\SparePart::class)
                    <a class="action-icon" href="{{ route('assets.spare-parts.create') }}"><i class="bi bi-box-seam"></i><span>{{ __('gmao.assets.part_new') }}</span></a>
                @endcan
            </div>
        </section>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @yield('content')
    </main>
</div>

<div class="modal fade" id="notificationsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">{{ __('gmao.common.notifications') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                @forelse(auth()->user()->notifications()->latest()->take(20)->get() as $notification)
                    <div class="notif-row {{ $notification->read_at ? 'read' : 'unread' }}">
                        <div><strong>{{ $notification->data['title'] ?? __('gmao.common.notifications') }}</strong><p class="mb-0">{{ $notification->data['message'] ?? '' }}</p></div>
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">@csrf
                                <button class="btn btn-sm btn-primary">{{ __('gmao.common.mark_read') }}</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('gmao.common.none') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
window.gmaoI18n = {
    weatherNow: @json(__('gmao.weather_now')),
    wind: @json(__('gmao.wind')),
    weatherFail: @json(__('gmao.weather_fail'))
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('js/gmao.js') }}"></script>
@stack('scripts')
</body>
</html>


