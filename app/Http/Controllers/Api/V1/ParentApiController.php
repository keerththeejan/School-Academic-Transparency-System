<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailySummary;
use App\Services\DiscrepancyService;
use App\Services\SummaryWording;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ParentApiController extends Controller
{
    public function children(Request $request)
    {
        $parent = $this->parent($request);
        $children = $parent->students()->with(['schoolClass', 'school'])->get()->map(fn ($student) => [
            'id' => $student->id,
            'full_name' => $student->full_name,
            'class' => $student->schoolClass?->label(),
            'school' => $student->school?->school_name,
        ]);

        return ApiResponse::ok($children);
    }

    public function summaries(Request $request)
    {
        $parent = $this->parent($request);
        $classIds = $parent->students()->pluck('students.class_id');
        $rows = DailySummary::query()->with('schoolClass')->whereIn('class_id', $classIds)->where('status', 'published')->latest('date')->paginate(20);

        return ApiResponse::ok($rows);
    }

    public function summary(Request $request, int $summary, SummaryWording $wording)
    {
        $parent = $this->parent($request);
        $record = DailySummary::query()->with(['items', 'school', 'schoolClass'])->where('status', 'published')->findOrFail($summary);
        abort_unless($parent->students()->where('class_id', $record->class_id)->exists(), 403);

        return ApiResponse::ok([
            'school' => $record->school->school_name,
            'class' => $record->schoolClass->label(),
            'date' => $record->date->toDateString(),
            'day_note' => $record->day_note,
            'items' => $record->items->map(fn ($item) => [
                'period' => $item->period,
                'subject' => $item->scheduled_subject,
                'status' => $wording->fromItem($item),
            ]),
        ]);
    }

    public function discrepancy(Request $request, DiscrepancyService $service)
    {
        $data = $request->validate([
            'student_id' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'period' => ['nullable', 'integer'],
            'description' => ['required', 'string', 'max:2000'],
        ]);
        $case = $service->report($this->parent($request), $data);

        return ApiResponse::ok(['id' => $case->id], 'Operation successful', 201);
    }

    private function parent(Request $request)
    {
        abort_unless($request->user()->hasPermission('children.view.own'), 403);

        return $request->user()->parentProfile ?? abort(403);
    }
}
