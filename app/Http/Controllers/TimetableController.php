<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Timetable;
use App\Models\TimetableVersion;
use App\Services\AuditLogger;
use App\Services\TimetableService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('timetable.manage') || $request->user()->hasPermission('timetable.verify'), 403);
        $schoolId = $this->schoolId($request);
        $versions = TimetableVersion::query()
            ->with(['entries.schoolClass', 'entries.subject', 'entries.teacher'])
            ->where('school_id', $schoolId)
            ->orderByDesc('version_number')
            ->get();

        return view('timetable.index', [
            'versions' => $versions,
            'classes' => SchoolClass::query()->where('school_id', $schoolId)->where('status', 'active')->get(),
            'subjects' => Subject::query()->where('school_id', $schoolId)->where('status', 'active')->get(),
            'teachers' => Teacher::query()->where('school_id', $schoolId)->where('status', 'active')->get(),
            'showTeachers' => $request->user()->seesTeacherIdentity(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->hasPermission('timetable.manage'), 403);

        return view('timetable.create');
    }

    public function store(Request $request, AuditLogger $audit)
    {
        abort_unless($request->user()->hasPermission('timetable.manage'), 403);
        $data = $request->validate([
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);
        $schoolId = $this->schoolId($request);
        $number = (int) TimetableVersion::query()->where('school_id', $schoolId)->max('version_number') + 1;
        $version = TimetableVersion::query()->create([
            'school_id' => $schoolId,
            'version_number' => $number,
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);
        $audit->log($request->user(), 'CREATE', 'timetable_version', $version->id, null, $version->toArray(), null, $schoolId);

        return redirect()->route('timetable.index')->with('status', __('ui.created'));
    }

    public function storeEntry(Request $request, int $version, AuditLogger $audit)
    {
        abort_unless($request->user()->hasPermission('timetable.manage'), 403);
        $record = TimetableVersion::query()->visibleTo($request->user())->findOrFail($version);
        abort_if(in_array($record->status, ['published', 'archived'], true), 403);
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'period' => ['required', 'integer', 'between:1,12'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $entry = DB::transaction(function () use ($record, $data, $request, $audit) {
            $entry = Timetable::query()->updateOrCreate(
                [
                    'version_id' => $record->id,
                    'class_id' => $data['class_id'],
                    'day_of_week' => $data['day_of_week'],
                    'period' => $data['period'],
                ],
                [
                    'subject_id' => $data['subject_id'],
                    'teacher_id' => $data['teacher_id'] ?: null,
                    'effective_from' => $record->effective_from,
                    'effective_to' => $record->effective_to,
                    'status' => 'active',
                ]
            );
            $audit->log($request->user(), 'UPDATE', 'timetable', $entry->id, null, $entry->toArray(), 'Period saved on version '.$record->version_number, $record->school_id);

            return $entry;
        });

        return back()->with('status', __('ui.saved'));
    }

    public function approve(Request $request, int $version, TimetableService $service)
    {
        abort_unless($request->user()->hasPermission('timetable.approve'), 403);
        $record = TimetableVersion::query()->visibleTo($request->user())->findOrFail($version);
        $service->approve($request->user(), $record);

        return back()->with('status', __('ui.approved'));
    }

    public function publish(Request $request, int $version, TimetableService $service)
    {
        abort_unless($request->user()->hasPermission('timetable.publish'), 403);
        $record = TimetableVersion::query()->visibleTo($request->user())->findOrFail($version);
        $service->publish($request->user(), $record);

        return back()->with('status', __('ui.published'));
    }
}
