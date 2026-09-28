<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\DiscrepancyCase;
use App\Models\PortalNotification;
use App\Models\Student;
use App\Services\DiscrepancyService;
use App\Services\SummaryWording;
use Illuminate\Http\Request;

class ParentPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $parent = $this->parent($request);
        $children = $parent->students()->with(['schoolClass', 'school'])->get();
        $summaries = [];
        foreach ($children as $child) {
            $summaries[$child->id] = DailySummary::query()
                ->where('class_id', $child->class_id)
                ->whereDate('date', now()->toDateString())
                ->where('status', 'published')
                ->first();
        }

        return view('parent.dashboard', compact('children', 'summaries'));
    }

    public function summaries(Request $request)
    {
        $parent = $this->parent($request);
        $classIds = $parent->students()->pluck('class_id');
        $summaries = DailySummary::query()
            ->with(['schoolClass', 'school'])
            ->whereIn('class_id', $classIds)
            ->where('status', 'published')
            ->latest('date')
            ->paginate(15);

        return view('parent.summaries', compact('summaries'));
    }

    public function summary(Request $request, int $summary, SummaryWording $wording)
    {
        $parent = $this->parent($request);
        $record = DailySummary::query()->with(['items.deviation', 'school', 'schoolClass'])->where('status', 'published')->findOrFail($summary);
        $student = $parent->students()->where('class_id', $record->class_id)->first();
        abort_unless($student, 403);
        $lines = $record->items->mapWithKeys(fn ($item) => [$item->id => $wording->fromItem($item)]);

        return view('parent.summary', ['summary' => $record, 'student' => $student, 'lines' => $lines]);
    }

    public function discrepancyForm(Request $request)
    {
        $children = $this->parent($request)->students()->with('schoolClass')->get();

        return view('parent.discrepancy', [
            'children' => $children,
            'selectedStudent' => $request->integer('student_id'),
            'selectedDate' => $request->input('date', now()->toDateString()),
        ]);
    }

    public function discrepancyStore(Request $request, DiscrepancyService $service)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'period' => ['nullable', 'integer', 'min:1', 'max:12'],
            'description' => ['required', 'string', 'max:2000'],
        ]);
        $case = $service->report($this->parent($request), $data);

        return redirect()->route('parent.case', $case)->with('status', __('ui.case_opened'));
    }

    public function caseShow(Request $request, int $case)
    {
        $parent = $this->parent($request);
        $record = DiscrepancyCase::query()->with('feedback.student')->findOrFail($case);
        abort_unless((int) $record->feedback->parent_id === (int) $parent->id, 403);

        return view('parent.case', ['case' => $record]);
    }

    public function notifications(Request $request)
    {
        $notes = PortalNotification::query()->where('user_id', $request->user()->id)->latest()->paginate(20);
        PortalNotification::query()->where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return view('parent.notifications', compact('notes'));
    }

    private function parent(Request $request)
    {
        abort_unless($request->user()->hasPermission('children.view.own'), 403);
        $parent = $request->user()->parentProfile;
        abort_unless($parent, 403);

        return $parent;
    }
}
