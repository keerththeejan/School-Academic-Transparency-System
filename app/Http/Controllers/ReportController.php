<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports)
    {
        abort_unless($request->user()->hasAnyPermission(['reports.view.school', 'reports.view.aggregate']), 403);
        $filters = $request->only(['from', 'to', 'school_id', 'zone_id', 'district_id', 'province_id']);

        return view('reports.index', ['report' => $reports->monthly($request->user(), $filters)]);
    }

    public function export(Request $request, ReportService $reports, AuditLogger $audit)
    {
        abort_unless($request->user()->hasPermission('reports.export'), 403);
        $filters = $request->only(['from', 'to', 'school_id', 'zone_id', 'district_id', 'province_id']);
        $audit->log($request->user(), 'EXPORT', 'report', null, null, $filters, $request->string('format'), $request->user()->school_id);

        return match ($request->string('format')->toString()) {
            'pdf' => $reports->pdf($request->user(), $filters),
            'xlsx' => $reports->xlsx($request->user(), $filters),
            default => $reports->csv($request->user(), $filters),
        };
    }
}
