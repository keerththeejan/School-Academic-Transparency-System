<?php

namespace App\Http\Controllers;

use App\Models\DiscrepancyCase;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasAnyPermission(['students.manage', 'schools.view', 'deviations.monitor']), 403);
        $term = trim($request->string('q')->toString());
        $like = '%'.addcslashes($term, '%_').'%';
        $groups = [];

        if ($term !== '') {
            $groups[__('ui.students')] = Student::query()->visibleTo($request->user())
                ->where(fn ($q) => $q->where('full_name', 'like', $like)->orWhere('admission_number', 'like', $like)->orWhere('student_identifier', 'like', $like))
                ->limit(10)->get()->map(fn ($row) => $row->full_name.' · '.$row->admission_number);
            if ($request->user()->seesTeacherIdentity()) {
                $groups[__('ui.teachers')] = Teacher::query()->visibleTo($request->user())->where('full_name', 'like', $like)->limit(10)->pluck('full_name');
            }
            $schoolIds = app(\App\Services\SchoolAccess::class)->allowedSchoolIds($request->user());
            $groups[__('ui.parents')] = StudentParent::query()
                ->when($schoolIds !== null, fn ($q) => $q->whereHas('students', fn ($students) => $students->whereIn('school_id', $schoolIds ?: [-1])))
                ->where('full_name', 'like', $like)->limit(10)->pluck('full_name');
            $groups[__('ui.schools')] = School::query()->visibleTo($request->user())->where('school_name', 'like', $like)->limit(10)->pluck('school_name');
            $groups[__('ui.classes')] = SchoolClass::query()->visibleTo($request->user())->where('grade', 'like', $like)->limit(10)->get()->map(fn ($row) => $row->label());
            $groups[__('ui.subjects')] = Subject::query()->visibleTo($request->user())->where('subject_name', 'like', $like)->limit(10)->pluck('subject_name');
            $groups[__('ui.discrepancies')] = DiscrepancyCase::query()->visibleTo($request->user())->whereHas('feedback', fn ($q) => $q->where('description', 'like', $like))->limit(10)->get()->map(fn ($row) => '#'.$row->id.' · '.$row->status);
        }

        return view('search.index', ['term' => $term, 'groups' => $groups]);
    }
}
