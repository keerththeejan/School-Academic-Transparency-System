<?php

namespace App\Http\Controllers;

use App\Models\DailySchedule;
use App\Models\Teacher;
use App\Services\DailyScheduleService;
use App\Support\DeviationCatalog;
use Illuminate\Http\Request;

class TeacherPortalController extends Controller
{
    public function dashboard(Request $request, DailyScheduleService $schedules)
    {
        $teacher = $this->teacher($request);
        $schedules->ensureForSchool($teacher->school, now());
        $slots = DailySchedule::query()
            ->with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacher->id)
            ->whereDate('date', now()->toDateString())
            ->orderBy('period')
            ->get();

        return view('teacher.dashboard', compact('slots'));
    }

    public function deviate(Request $request, int $schedule)
    {
        $teacher = $this->teacher($request);
        $slot = DailySchedule::query()->with(['schoolClass', 'subject'])->where('teacher_id', $teacher->id)->findOrFail($schedule);
        $teachers = Teacher::query()->where('school_id', $teacher->school_id)->where('status', 'active')->orderBy('full_name')->get();

        return view('teacher.deviate', [
            'slot' => $slot,
            'teachers' => $teachers,
            'types' => DeviationCatalog::grouped(),
        ]);
    }

    private function teacher(Request $request): Teacher
    {
        abort_unless($request->user()->hasPermission('timetable.view.own'), 403);
        $teacher = $request->user()->teacherProfile;
        abort_unless($teacher, 403);

        return $teacher;
    }
}
