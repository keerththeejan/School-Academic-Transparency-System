<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboards)
    {
        $user = $request->user();
        $role = $user->primaryRole();

        return match ($role) {
            'parent' => redirect()->route('parent.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'sdc_viewer' => view('dashboard.aggregate', [
                'title' => __('ui.aggregate_only'),
                'overview' => $dashboards->aggregate($user, $request->only(['from', 'to'])),
            ]),
            'zonal_admin' => view('dashboard.aggregate', [
                'title' => __('ui.zones'),
                'overview' => $dashboards->aggregate($user, $request->only(['from', 'to', 'school_id'])),
            ]),
            'ministry_admin', 'provincial_admin' => view('dashboard.aggregate', [
                'title' => __('ui.aggregate_only'),
                'overview' => $dashboards->aggregate($user, $request->only(['from', 'to', 'province_id', 'zone_id'])),
            ]),
            default => view('dashboard.school', [
                'overview' => $dashboards->school($user),
            ]),
        };
    }
}
