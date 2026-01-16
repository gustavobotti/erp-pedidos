<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard with order statistics
     */
    public function index()
    {
        $data = $this->dashboardService->getDashboardData();

        return Inertia::render('Dashboard', [
            'dashboardData' => $data,
        ]);
    }

    /**
     * Refresh dashboard cache
     */
    public function refresh()
    {
        $this->dashboardService->clearCache();

        return redirect()->route('dashboard')->with('success', 'Dashboard atualizado com sucesso!');
    }
}

