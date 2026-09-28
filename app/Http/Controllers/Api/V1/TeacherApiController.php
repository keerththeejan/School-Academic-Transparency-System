<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DailySchedule;
use App\Services\DailyScheduleService;
use App\Services\DeviationService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class TeacherApiController extends Controller
{
    public function today(Request $request, DailyScheduleService $schedules)
    {
        $teacher = $request->user()->teacherProfile ?? abort(403);
        abort_unless($request->user()->hasPermission('timetable.view.own'), 403);
        $schedules->ensureForSchool($teacher->school, now());
        $slots = DailySchedule::query()->with(['schoolClass', 'subject'])->where('teacher_id', $teacher->id)->whereDate('date', now())->orderBy('period')->get();

        return ApiResponse::ok($slots->map(fn ($slot) => [
            'id' => $slot->id,
            'time' => $schedules->periodLabel($slot->period, $slot->school_id),
            'class' => $slot->schoolClass->label(),
            'subject' => $slot->subject?->subject_name,
            'period' => $slot->period,
        ]));
    }

    public function deviate(Request $request, DeviationService $service)
    {
        abort_unless($request->user()->hasPermission('deviations.report'), 403);
        $data = $request->validate([
            'daily_schedule_id' => ['required', 'integer'],
            'deviation_type' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'relief_teacher_id' => ['nullable', 'integer'],
            'reason' => ['nullable', 'string'],
        ]);

        $deviation = $service->record($request->user(), $data);

        return ApiResponse::ok(['id' => $deviation->id], 'Operation successful', 201);
    }
}
