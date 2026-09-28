<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\DailyDeviation;
use App\Models\DailySchedule;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Services\DailyScheduleService;
use App\Services\DeviationService;
use App\Support\DeviationCatalog;
use Illuminate\Http\Request;

class DeviationController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('deviations.monitor'), 403);
        $query = DailyDeviation::query()->visibleTo($request->user())->with('schoolClass');
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date('date'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->input('focus') === 'not_conducted') {
            $query->whereIn('deviation_type', DeviationCatalog::NOT_CONDUCTED);
        }
        if ($request->filled('deviation_type')) {
            $query->where('deviation_type', $request->string('deviation_type'));
        }

        return view('deviations.index', [
            'records' => $query->latest('date')->paginate(20)->withQueryString(),
            'showTeachers' => $request->user()->seesTeacherIdentity(),
        ]);
    }

    public function create(Request $request, DailyScheduleService $schedules)
    {
        abort_unless($request->user()->hasAnyPermission(['deviations.record', 'deviations.report']), 403);
        $schoolId = $this->schoolId($request);
        $schedules->ensureForSchool(\App\Models\School::query()->findOrFail($schoolId), $request->date('date') ?? now());
        $slots = DailySchedule::query()
            ->with(['schoolClass', 'subject'])
            ->where('school_id', $schoolId)
            ->when($request->filled('class_id'), fn ($q) => $q->where('class_id', $request->integer('class_id')))
            ->whereDate('date', $request->input('date', now()->toDateString()))
            ->orderBy('period')
            ->get();

        return view('deviations.create', [
            'slots' => $slots,
            'classes' => SchoolClass::query()->where('school_id', $schoolId)->where('status', 'active')->get(),
            'teachers' => Teacher::query()->where('school_id', $schoolId)->where('status', 'active')->get(),
            'types' => DeviationCatalog::grouped(),
        ]);
    }

    public function store(Request $request, DeviationService $service)
    {
        $data = $request->validate([
            'daily_schedule_id' => ['required', 'integer'],
            'deviation_type' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:255'],
            'relief_teacher_id' => ['nullable', 'integer'],
            'action_taken' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        $service->record($request->user(), $data);

        return redirect()->route($request->user()->hasRole('teacher') ? 'teacher.dashboard' : 'deviations.index')->with('status', __('ui.created'));
    }

    public function show(Request $request, int $deviation)
    {
        abort_unless($request->user()->hasPermission('deviations.monitor'), 403);
        $record = DailyDeviation::query()->visibleTo($request->user())->with(['schoolClass', 'reliefTeacher'])->findOrFail($deviation);
        $audits = AuditLog::query()->with('user')->where('record_type', 'daily_deviation')->where('record_id', $record->id)->orderBy('timestamp')->get();

        return view('deviations.show', [
            'deviation' => $record,
            'audits' => $audits,
            'showTeachers' => $request->user()->seesTeacherIdentity(),
        ]);
    }

    public function approve(Request $request, int $deviation, DeviationService $service)
    {
        abort_unless($request->user()->hasPermission('deviations.approve'), 403);
        $record = DailyDeviation::query()->visibleTo($request->user())->findOrFail($deviation);
        $service->approve($request->user(), $record);

        return back()->with('status', __('ui.approved'));
    }

    public function reject(Request $request, int $deviation, DeviationService $service)
    {
        abort_unless($request->user()->hasPermission('deviations.approve'), 403);
        $data = $request->validate(['reason' => ['required', 'string', 'max:500']]);
        $record = DailyDeviation::query()->visibleTo($request->user())->findOrFail($deviation);
        $service->reject($request->user(), $record, $data['reason']);

        return back()->with('status', __('ui.rejected'));
    }

    public function correct(Request $request, int $deviation, DeviationService $service)
    {
        abort_unless($request->user()->hasPermission('deviations.approve'), 403);
        $data = $request->validate([
            'correction_reason' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'action_taken' => ['nullable', 'string', 'max:255'],
            'deviation_type' => ['nullable', 'string'],
            'relief_teacher_id' => ['nullable', 'integer'],
        ]);
        $record = DailyDeviation::query()->visibleTo($request->user())->findOrFail($deviation);
        $service->correct($request->user(), $record, $data, $data['correction_reason']);

        return back()->with('status', __('ui.updated'));
    }
}
