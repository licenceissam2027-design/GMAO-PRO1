<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardMetricsService $dashboardMetricsService)
    {
    }

    public function index(): View
    {
        $metrics = $this->dashboardMetricsService->build(auth()->user());

        return view('dashboard.index', $metrics);
    }
}

