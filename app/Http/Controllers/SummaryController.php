<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\School;
use App\Services\DailySummaryService;
use App\Services\SummaryWording;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('summaries.view'), 403);
        $records = DailySummary::query()->visibleTo($request->user())->with('schoolClass')->latest('date')->paginate(20);

        return view('summaries.index', compact('records'));
    }

    public function show(Request $request, int $summary, SummaryWording $wording)
    {
        abort_unless($request->user()->hasPermission('summaries.view'), 403);
        $record = DailySummary::query()->visibleTo($request->user())->with(['items.deviation', 'school', 'schoolClass'])->findOrFail($summary);
        $lines = $record->items->mapWithKeys(fn ($item) => [$item->id => $wording->fromItem($item)]);

        return view('summaries.show', [
            'summary' => $record,
            'lines' => $lines,
            'showTeachers' => $request->user()->seesTeacherIdentity(),
        ]);
    }

    public function generate(Request $request, DailySummaryService $service)
    {
        abort_unless($request->user()->hasPermission('summaries.generate'), 403);
        $data = $request->validate(['date' => ['required', 'date']]);
        $school = School::query()->findOrFail($this->schoolId($request));
        $service->generateForSchool($school, Carbon::parse($data['date']), true, true);

        return back()->with('status', __('ui.saved'));
    }
}
