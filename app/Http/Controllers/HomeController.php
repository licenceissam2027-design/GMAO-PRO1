<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceTask;
use App\Models\Project;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $projects = $this->scopeByUser(Project::query(), $user);
        $requests = $this->scopeByUser(MaintenanceRequest::query(), $user);
        $tasks = $this->scopeByUser(MaintenanceTask::query(), $user);
        $spareParts = $this->scopeByUser(SparePart::query(), $user);

        $stats = [
            'projects_total' => (clone $projects)->count(),
            'projects_in_progress' => (clone $projects)->where('status', 'in_progress')->count(),
            'requests_pending' => (clone $requests)->where('status', 'pending')->count(),
            'tasks_completed' => (clone $tasks)->where('status', 'completed')->count(),
            'low_stock_parts' => (clone $spareParts)->whereColumn('current_stock', '<=', 'minimum_stock')->count(),
        ];

        return view('home', [
            'stats' => $stats,
            'recentRequests' => (clone $requests)->latest()->take(5)->get(),
        ]);
    }

    private function scopeByUser(Builder $query, ?User $user): Builder
    {
        if (!$user || $user->isRole('super_admin') || empty($user->sector)) {
            return $query;
        }

        return $query->where('sector', $user->sector);
    }
}

