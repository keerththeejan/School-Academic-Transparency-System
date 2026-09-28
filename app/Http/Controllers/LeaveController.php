<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherLeave;
use App\Services\AuditLogger;
use App\Services\LeaveService;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('leave.manage'), 403);

        return view('leave.index', [
            'records' => TeacherLeave::query()->visibleTo($request->user())->with('teacher')->latest()->paginate(20),
            'teachers' => Teacher::query()->visibleTo($request->user())->where('status', 'active')->orderBy('full_name')->get(),
            'showTeachers' => $request->user()->seesTeacherIdentity(),
        ]);
    }

    public function store(Request $request, AuditLogger $audit)
    {
        abort_unless($request->user()->hasPermission('leave.manage'), 403);
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'leave_type' => ['required', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $teacher = Teacher::query()->visibleTo($request->user())->findOrFail($data['teacher_id']);
        $leave = TeacherLeave::query()->create([
            ...$data,
            'school_id' => $teacher->school_id,
            'status' => 'pending',
        ]);
        $audit->log($request->user(), 'CREATE', 'teacher_leave', $leave->id, null, $leave->toArray(), $leave->reason, $teacher->school_id);

        return back()->with('status', __('ui.created'));
    }

    public function approve(Request $request, int $leave, LeaveService $service)
    {
        abort_unless($request->user()->hasPermission('leave.approve'), 403);
        $record = TeacherLeave::query()->visibleTo($request->user())->findOrFail($leave);
        $service->approve($request->user(), $record);

        return back()->with('status', __('ui.approved'));
    }
}
