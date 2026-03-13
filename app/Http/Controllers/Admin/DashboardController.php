<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService)
    {
        // 
    }

    public function index(): View
    {
        $data = $this->dashboardService->getDashboardOverview();
        // dd($data);
        return view('pages.admin.dashboard', $data);
    }
}
