<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['ar', 'fr', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active', 'no.cache'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->middleware('role:super_admin,manager')->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('projects.store');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->middleware(['role:super_admin', 'throttle:120,1'])->name('projects.destroy');
    Route::post('/projects/{project}/phases', [ProjectController::class, 'storePhase'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('projects.phases.store');
    Route::patch('/projects/phases/{projectPhase}', [ProjectController::class, 'updatePhase'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('projects.phases.update');
    Route::delete('/projects/phases/{projectPhase}', [ProjectController::class, 'destroyPhase'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('projects.phases.destroy');

    Route::get('/maintenance/requests', [MaintenanceController::class, 'requests'])->name('maintenance.requests');
    Route::get('/maintenance/requests/create', [MaintenanceController::class, 'createRequest'])->name('maintenance.requests.create');
    Route::post('/maintenance/requests', [MaintenanceController::class, 'storeRequest'])->middleware('throttle:120,1')->name('maintenance.requests.store');
    Route::get('/maintenance/requests/{maintenanceRequest}', [MaintenanceController::class, 'showRequest'])->name('maintenance.requests.show');
    Route::patch('/maintenance/requests/{maintenanceRequest}', [MaintenanceController::class, 'updateRequest'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('maintenance.requests.update');
    Route::delete('/maintenance/requests/{maintenanceRequest}', [MaintenanceController::class, 'destroyRequest'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('maintenance.requests.destroy');
    Route::patch('/maintenance/requests/{maintenanceRequest}/status', [MaintenanceController::class, 'updateRequestStatus'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('maintenance.requests.status');

    Route::get('/maintenance/plans', [MaintenanceController::class, 'plans'])->middleware('role:super_admin,manager,technician')->name('maintenance.plans');
    Route::get('/maintenance/plans/create', [MaintenanceController::class, 'createPlan'])->middleware('role:super_admin,manager,technician')->name('maintenance.plans.create');
    Route::post('/maintenance/plans', [MaintenanceController::class, 'storePlan'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('maintenance.plans.store');
    Route::patch('/maintenance/plans/{preventivePlan}', [MaintenanceController::class, 'updatePlan'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('maintenance.plans.update');
    Route::delete('/maintenance/plans/{preventivePlan}', [MaintenanceController::class, 'destroyPlan'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('maintenance.plans.destroy');

    Route::get('/maintenance/tasks', [MaintenanceController::class, 'tasks'])->middleware('role:super_admin,manager,technician')->name('maintenance.tasks');
    Route::get('/maintenance/rounds', [MaintenanceController::class, 'rounds'])->middleware('role:super_admin,manager,technician')->name('maintenance.rounds');
    Route::get('/maintenance/tasks/create', [MaintenanceController::class, 'createTask'])->middleware('role:super_admin,manager')->name('maintenance.tasks.create');
    Route::post('/maintenance/tasks', [MaintenanceController::class, 'storeTask'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('maintenance.tasks.store');
    Route::patch('/maintenance/tasks/{maintenanceTask}', [MaintenanceController::class, 'updateTask'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('maintenance.tasks.update');
    Route::patch('/maintenance/tasks/{maintenanceTask}/execution', [MaintenanceController::class, 'updateRoundExecution'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('maintenance.tasks.execution');
    Route::delete('/maintenance/tasks/{maintenanceTask}', [MaintenanceController::class, 'destroyTask'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('maintenance.tasks.destroy');

    Route::get('/assets/industrial', [AssetController::class, 'industrial'])->name('assets.industrial');
    Route::get('/assets/industrial/create', [AssetController::class, 'createIndustrial'])->middleware('role:super_admin,manager,technician')->name('assets.industrial.create');
    Route::post('/assets/industrial', [AssetController::class, 'storeIndustrial'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.industrial.store');
    Route::patch('/assets/industrial/{industrialMachine}', [AssetController::class, 'updateIndustrial'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.industrial.update');
    Route::delete('/assets/industrial/{industrialMachine}', [AssetController::class, 'destroyIndustrial'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.industrial.destroy');

    Route::get('/assets/technical', [AssetController::class, 'technical'])->name('assets.technical');
    Route::get('/assets/technical/create', [AssetController::class, 'createTechnical'])->middleware('role:super_admin,manager,technician')->name('assets.technical.create');
    Route::post('/assets/technical', [AssetController::class, 'storeTechnical'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.technical.store');
    Route::patch('/assets/technical/{technicalAsset}', [AssetController::class, 'updateTechnical'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.technical.update');
    Route::delete('/assets/technical/{technicalAsset}', [AssetController::class, 'destroyTechnical'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.technical.destroy');

    Route::get('/assets/spare-parts', [AssetController::class, 'spareParts'])->name('assets.spare-parts');
    Route::get('/assets/spare-parts/create', [AssetController::class, 'createSparePart'])->middleware('role:super_admin,manager,technician')->name('assets.spare-parts.create');
    Route::post('/assets/spare-parts', [AssetController::class, 'storeSparePart'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.spare-parts.store');
    Route::patch('/assets/spare-parts/{sparePart}', [AssetController::class, 'updateSparePart'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.spare-parts.update');
    Route::delete('/assets/spare-parts/{sparePart}', [AssetController::class, 'destroySparePart'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.spare-parts.destroy');

    Route::get('/assets/experts', [AssetController::class, 'experts'])->middleware('role:super_admin,manager')->name('assets.experts');
    Route::get('/assets/experts/create', [AssetController::class, 'createExpert'])->middleware('role:super_admin,manager')->name('assets.experts.create');
    Route::post('/assets/experts', [AssetController::class, 'storeExpert'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.experts.store');
    Route::patch('/assets/experts/{expertMission}', [AssetController::class, 'updateExpert'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.experts.update');
    Route::delete('/assets/experts/{expertMission}', [AssetController::class, 'destroyExpert'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.experts.destroy');

    Route::get('/assets/reports', [AssetController::class, 'reports'])->name('assets.reports');
    Route::get('/assets/reports/create', [AssetController::class, 'createReport'])->middleware('role:super_admin,manager,technician')->name('assets.reports.create');
    Route::post('/assets/reports', [AssetController::class, 'storeReport'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.reports.store');
    Route::patch('/assets/reports/{reportFile}', [AssetController::class, 'updateReport'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.reports.update');
    Route::delete('/assets/reports/{reportFile}', [AssetController::class, 'destroyReport'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.reports.destroy');

    Route::get('/assets/logistics', [AssetController::class, 'logistics'])->name('assets.logistics');
    Route::get('/assets/logistics/create', [AssetController::class, 'createLogistic'])->middleware('role:super_admin,manager,technician')->name('assets.logistics.create');
    Route::post('/assets/logistics', [AssetController::class, 'storeLogistic'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.logistics.store');
    Route::patch('/assets/logistics/{logisticAsset}', [AssetController::class, 'updateLogistic'])->middleware(['role:super_admin,manager,technician', 'throttle:120,1'])->name('assets.logistics.update');
    Route::delete('/assets/logistics/{logisticAsset}', [AssetController::class, 'destroyLogistic'])->middleware(['role:super_admin,manager', 'throttle:120,1'])->name('assets.logistics.destroy');

    Route::get('/team', [TeamController::class, 'index'])->middleware('role:super_admin,manager')->name('team.index');
    Route::get('/team/create', [TeamController::class, 'create'])->middleware('role:super_admin')->name('team.create');
    Route::post('/team', [TeamController::class, 'store'])->middleware(['role:super_admin', 'throttle:120,1'])->name('team.store');
    Route::get('/team/{user}/edit', [TeamController::class, 'edit'])->middleware('role:super_admin')->name('team.edit');
    Route::patch('/team/{user}', [TeamController::class, 'update'])->middleware(['role:super_admin', 'throttle:120,1'])->name('team.update');
    Route::delete('/team/{user}', [TeamController::class, 'destroy'])->middleware(['role:super_admin', 'throttle:120,1'])->name('team.destroy');

    Route::post('/notifications/read/{id}', function (string $id) {
        $notification = auth()->user()?->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }

        return back();
    })->middleware('throttle:120,1')->name('notifications.read');
});

