<?php

namespace App\Http\Controllers;

use App\Models\SchoolCalendar;
use App\Services\AuditLogger;
use App\Support\CalendarTypes;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasPermission('calendar.manage'), 403);
        $records = SchoolCalendar::query()->visibleTo($request->user())->orderByDesc('date')->paginate(30);

        return view('calendar.index', compact('records'));
    }

    public function store(Request $request, AuditLogger $audit)
    {
        abort_unless($request->user()->hasPermission('calendar.manage'), 403);
        $data = $request->validate([
            'date' => ['required', 'date'],
            'calendar_type' => ['required', 'in:'.implode(',', CalendarTypes::all())],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_school_day' => ['required', 'boolean'],
            'substitutes_day_of_week' => ['nullable', 'integer', 'between:1,7'],
        ]);
        $schoolId = $this->schoolId($request);
        $row = SchoolCalendar::query()->updateOrCreate(
            ['school_id' => $schoolId, 'date' => $data['date']],
            [
                'calendar_type' => $data['calendar_type'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_school_day' => $request->boolean('is_school_day'),
                'substitutes_day_of_week' => $data['substitutes_day_of_week'] ?? null,
                'created_by' => $request->user()->id,
            ]
        );
        $audit->log($request->user(), 'UPDATE', 'school_calendar', $row->id, null, $row->toArray(), null, $schoolId);

        return back()->with('status', __('ui.saved'));
    }
}
