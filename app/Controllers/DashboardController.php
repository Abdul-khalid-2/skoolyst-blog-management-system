<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\View;
use Skoolyst\Services\DashboardService;

/** Shared dashboard controller. Module-specific dashboards should extend this pattern. */
class DashboardController {
    public function __construct(private DashboardService $dashboard = new DashboardService()) {}

    public function index(): void {
        View::render('admin/dashboard', [
            'title' => 'Dashboard',
            'activeNav' => 'dashboard',
            'stats' => $this->dashboard->stats(),
            'recentPosts' => $this->dashboard->recentPosts(),
        ], 'admin');
    }
}
