<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use App\Services\ReportService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class SchoolApiController extends Controller
{
    public function dashboard(Request $request, DashboardService $dashboards)
    {
        abort_unless($request->user()->hasAnyPermission(['school.admin', 'deviations.monitor', 'aggregate.view']), 403);
        if ($request->user()->isAggregateViewer() || $request->user()->hasRole('ministry_admin')) {
            return ApiResponse::ok($dashboards->aggregate($request->user(), $request->only(['from', 'to'])));
        }

        return ApiResponse::ok($dashboards->school($request->user()));
    }

    public function report(Request $request, ReportService $reports)
    {
        abort_unless($request->user()->hasAnyPermission(['reports.view.school', 'reports.view.aggregate']), 403);

        return ApiResponse::ok($reports->monthly($request->user(), $request->only(['from', 'to', 'school_id', 'zone_id', 'district_id', 'province_id'])));
    }
}
