<?php

namespace App\Http\Controllers;

use App\Models\DailyDeviation;
use App\Models\ReliefAssignment;
use App\Models\Teacher;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReliefController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyPermission(['relief.manage', 'relief.provide']), 403);
        $records = ReliefAssignment::query()->visibleTo($request->user())->with('schoolClass')->latest('date')->paginate(20);
        $teachers = Teacher::query()->visibleTo($request->user())->where('status', 'active')->orderBy('full_name')->get();

        return view('relief.index', compact('records', 'teachers'));
    }

    public function update(Request $request, int $relief, AuditLogger $audit)
    {
        abort_unless($request->user()->hasAnyPermission(['relief.manage', 'relief.provide']), 403);
        $data = $request->validate(['relief_teacher_id' => ['nullable', 'exists:teachers,id']]);
        $record = ReliefAssignment::query()->visibleTo($request->user())->findOrFail($relief);

        DB::transaction(function () use ($request, $record, $data, $audit) {
            $old = $record->only(['relief_teacher_id', 'status']);
            $record->forceFill([
                'relief_teacher_id' => $data['relief_teacher_id'] ?: null,
                'status' => $data['relief_teacher_id'] ? 'assigned' : 'unassigned',
                'assigned_by' => $request->user()->id,
                'assigned_at' => now(),
            ])->save();

            if ($record->daily_schedule_id) {
                DailyDeviation::query()->where('daily_schedule_id', $record->daily_schedule_id)->where('status', '!=', 'rejected')->update([
                    'relief_teacher_id' => $record->relief_teacher_id,
                    'action_taken' => $record->relief_teacher_id ? 'relief_assigned' : 'relief_unavailable',
                ]);
            }

            $audit->log($request->user(), 'UPDATE', 'relief_assignment', $record->id, $old, $record->only(['relief_teacher_id', 'status']), null, $record->school_id);
        });

        return back()->with('status', __('ui.saved'));
    }
}
