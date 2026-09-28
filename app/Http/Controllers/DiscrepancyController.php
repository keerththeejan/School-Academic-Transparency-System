<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyCase;
use App\Services\DiscrepancyService;
use Illuminate\Http\Request;

class DiscrepancyController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('discrepancies.review'), 403);
        $records = DiscrepancyCase::query()
            ->visibleTo($request->user())
            ->with(['feedback.student.schoolClass'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20);

        return view('discrepancies.index', compact('records'));
    }

    public function show(Request $request, int $case)
    {
        abort_unless($request->user()->hasPermission('discrepancies.review'), 403);
        $record = DiscrepancyCase::query()->visibleTo($request->user())->with(['feedback.student.schoolClass', 'actions.user'])->findOrFail($case);

        return view('discrepancies.show', ['case' => $record]);
    }

    public function resolve(Request $request, int $case, DiscrepancyService $service)
    {
        abort_unless($request->user()->hasPermission('discrepancies.review'), 403);
        $data = $request->validate([
            'classification' => ['required', 'string'],
            'resolution' => ['required', 'string', 'max:2000'],
        ]);
        $record = DiscrepancyCase::query()->visibleTo($request->user())->findOrFail($case);
        $service->resolve($request->user(), $record, $data['classification'], $data['resolution']);

        return back()->with('status', __('ui.resolved'));
    }
}
