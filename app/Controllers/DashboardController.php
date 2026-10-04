<?php
declare(strict_types=1);

namespace Skoolyst\Controllers;

use Skoolyst\Core\View;
use Skoolyst\Services\DashboardService;

/** Shared dashboard controller. Module-specific dashboards should extend this pattern. */
class DashboardController {
    public function __construct(private DashboardService $dashboard = new DashboardService()) {}

    public function index(): void {
        // Authors/editors see only their own posts' numbers; admins see the whole site.
        $authorId = $this->dashboard->scopeFor(auth_user());

        View::render('admin/dashboard', [
            'title' => 'Dashboard',
            'activeNav' => 'dashboard',
            'stats' => $this->dashboard->stats($authorId),
            'recentPosts' => $this->dashboard->recentPosts($authorId),
            'ownOnly' => $authorId !== null,
        ], 'admin');
    }
}
