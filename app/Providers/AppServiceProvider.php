<?php

namespace App\Providers;

use App\Models\ExpertMission;
use App\Models\IndustrialMachine;
use App\Models\LogisticAsset;
use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\PreventivePlan;
use App\Models\Project;
use App\Models\ProjectPhase;
use App\Models\ReportFile;
use App\Models\SparePart;
use App\Models\TechnicalAsset;
use App\Models\User;
use App\Observers\AuditableObserver;
use App\Policies\ExpertMissionPolicy;
use App\Policies\IndustrialMachinePolicy;
use App\Policies\LogisticAssetPolicy;
use App\Policies\MaintenanceRequestPolicy;
use App\Policies\MaintenanceTaskPolicy;
use App\Policies\PreventivePlanPolicy;
use App\Policies\ProjectPolicy;
use App\Policies\ReportFilePolicy;
use App\Policies\SparePartPolicy;
use App\Policies\TechnicalAssetPolicy;
use App\Policies\UserPolicy;
use App\Services\DashboardMetricsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Contracts\MaintenanceRepositoryInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Eloquent\MaintenanceRepository;
use App\Repositories\Eloquent\ProjectRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MaintenanceRepositoryInterface::class, MaintenanceRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(MaintenanceRequest::class, MaintenanceRequestPolicy::class);
        Gate::policy(PreventivePlan::class, PreventivePlanPolicy::class);
        Gate::policy(MaintenanceTask::class, MaintenanceTaskPolicy::class);
        Gate::policy(IndustrialMachine::class, IndustrialMachinePolicy::class);
        Gate::policy(TechnicalAsset::class, TechnicalAssetPolicy::class);
        Gate::policy(SparePart::class, SparePartPolicy::class);
        Gate::policy(ExpertMission::class, ExpertMissionPolicy::class);
        Gate::policy(ReportFile::class, ReportFilePolicy::class);
        Gate::policy(LogisticAsset::class, LogisticAssetPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Project::observe(AuditableObserver::class);
        ProjectPhase::observe(AuditableObserver::class);
        MaintenanceRequest::observe(AuditableObserver::class);
        MaintenanceTask::observe(AuditableObserver::class);
        PreventivePlan::observe(AuditableObserver::class);
        IndustrialMachine::observe(AuditableObserver::class);
        TechnicalAsset::observe(AuditableObserver::class);
        SparePart::observe(AuditableObserver::class);
        LogisticAsset::observe(AuditableObserver::class);
        ExpertMission::observe(AuditableObserver::class);
        ReportFile::observe(AuditableObserver::class);

        View::composer('layouts.app', function ($view): void {
            $user = Auth::user();
            if (!$user) {
                return;
            }

            $cacheKey = "gmao:layout-kpi:user:{$user->id}:locale:" . app()->getLocale();
            $metrics = Cache::remember($cacheKey, now()->addSeconds(60), fn () => app(DashboardMetricsService::class)->build($user));
            $view->with('unifiedKpi', [
                'open_requests' => $metrics['kpiCards']['open_requests'] ?? 0,
                'critical_requests' => $metrics['kpiCards']['critical_requests'] ?? 0,
                'low_stock_parts' => $metrics['kpiCards']['low_stock_parts'] ?? 0,
                'availability_rate' => $metrics['kpiCards']['availability_rate'] ?? 0,
            ]);
        });
    }
}

