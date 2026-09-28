<?php

namespace App\Support;

use App\Models\AcademicYear;
use App\Models\District;
use App\Models\Province;
use App\Models\Role;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\StudentParent;
use App\Models\Subject;
use App\Models\Term;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Validation\Rule;

class DirectoryRegistry
{
    public static function get(string $key): array
    {
        $status = ['active' => __('ui.active'), 'inactive' => __('ui.inactive')];
        $all = [
            'provinces' => [
                'model' => Province::class,
                'permission' => 'provinces.manage',
                'title' => 'ui.provinces',
                'search' => ['name', 'code'],
                'columns' => [
                    ['key' => 'name', 'label' => __('ui.name')],
                    ['key' => 'code', 'label' => __('ui.code')],
                    ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
                ],
                'rules' => fn ($model) => [
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:20', Rule::unique('provinces', 'code')->ignore($model?->id)],
                    'status' => ['required', 'in:active,inactive'],
                ],
                'fields' => fn ($user, $model) => [
                    self::text('name', __('ui.name'), $model, true),
                    self::text('code', __('ui.code'), $model, true),
                    self::select('status', __('ui.status'), $status, $model, true),
                ],
            ],
            'districts' => [
                'model' => District::class,
                'permission' => 'districts.manage',
                'title' => 'ui.districts',
                'with' => ['province'],
                'search' => ['name', 'code'],
                'columns' => [
                    ['key' => 'name', 'label' => __('ui.name')],
                    ['key' => 'province.name', 'label' => __('ui.province')],
                    ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
                ],
                'rules' => fn ($model) => [
                    'province_id' => ['required', 'exists:provinces,id'],
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:20'],
                    'status' => ['required', 'in:active,inactive'],
                ],
                'fields' => fn ($user, $model) => [
                    self::select('province_id', __('ui.province'), Province::query()->orderBy('name')->pluck('name', 'id')->all(), $model, true),
                    self::text('name', __('ui.name'), $model, true),
                    self::text('code', __('ui.code'), $model, true),
                    self::select('status', __('ui.status'), $status, $model, true),
                ],
            ],
            'zones' => [
                'model' => Zone::class,
                'permission' => 'zones.manage',
                'title' => 'ui.zones',
                'with' => ['district'],
                'search' => ['name', 'code'],
                'columns' => [
                    ['key' => 'name', 'label' => __('ui.name')],
                    ['key' => 'district.name', 'label' => __('ui.district')],
                    ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
                ],
                'rules' => fn ($model) => [
                    'district_id' => ['required', 'exists:districts,id'],
                    'name' => ['required', 'string', 'max:255'],
                    'code' => ['required', 'string', 'max:20'],
                    'status' => ['required', 'in:active,inactive'],
                ],
                'fields' => fn ($user, $model) => [
                    self::select('district_id', __('ui.district'), District::query()->orderBy('name')->pluck('name', 'id')->all(), $model, true),
                    self::text('name', __('ui.name'), $model, true),
                    self::text('code', __('ui.code'), $model, true),
                    self::select('status', __('ui.status'), $status, $model, true),
                ],
            ],
            'schools' => [
                'model' => School::class,
                'permission' => 'schools.manage',
                'view_permission' => 'schools.view',
                'title' => 'ui.schools',
                'school_column' => 'id',
                'with' => ['zone'],
                'search' => ['school_name', 'school_code'],
                'columns' => [
                    ['key' => 'school_code', 'label' => __('ui.school_code')],
                    ['key' => 'school_name', 'label' => __('ui.school_name')],
                    ['key' => 'zone.name', 'label' => __('ui.zone')],
                    ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
                ],
                'rules' => fn ($model) => [
                    'province_id' => ['required', 'exists:provinces,id'],
                    'district_id' => ['required', 'exists:districts,id'],
                    'zone_id' => ['required', 'exists:zones,id'],
                    'school_code' => ['required', 'string', 'max:50', Rule::unique('schools', 'school_code')->ignore($model?->id)],
                    'school_name' => ['required', 'string', 'max:255'],
                    'address' => ['nullable', 'string', 'max:255'],
                    'language' => ['required', 'in:en,ta,si'],
                    'phone' => ['nullable', 'string', 'max:30'],
                    'email' => ['nullable', 'email'],
                    'status' => ['required', 'in:active,inactive'],
                    'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                ],
                'fields' => fn ($user, $model) => [
                    self::select('province_id', __('ui.province'), Province::query()->orderBy('name')->pluck('name', 'id')->all(), $model, true),
                    self::select('district_id', __('ui.district'), District::query()->orderBy('name')->pluck('name', 'id')->all(), $model, true),
                    self::select('zone_id', __('ui.zone'), Zone::query()->orderBy('name')->pluck('name', 'id')->all(), $model, true),
                    self::text('school_code', __('ui.school_code'), $model, true),
                    self::text('school_name', __('ui.school_name'), $model, true),
                    self::text('address', __('ui.address'), $model),
                    self::select('language', __('ui.language'), ['en' => __('ui.english'), 'ta' => __('ui.tamil'), 'si' => __('ui.sinhala')], $model, true),
                    self::text('phone', __('ui.phone'), $model),
                    self::text('email', __('ui.email'), $model, false, 'email'),
                    self::select('status', __('ui.status'), $status, $model, true),
                    ['name' => 'logo', 'label' => __('ui.logo'), 'type' => 'file'],
                ],
            ],
            'academic-years' => self::schoolScoped(AcademicYear::class, 'school.admin', 'ui.academic_years', ['name'], [
                ['key' => 'name', 'label' => __('ui.name')],
                ['key' => 'start_date', 'label' => __('ui.start_date')],
                ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
            ], fn ($model) => [
                'name' => ['required', 'string', 'max:50'],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after:start_date'],
                'status' => ['required', 'in:planned,active,closed'],
            ], fn ($user, $model) => [
                self::text('name', __('ui.name'), $model, true),
                self::text('start_date', __('ui.start_date'), $model, true, 'date'),
                self::text('end_date', __('ui.end_date'), $model, true, 'date'),
                self::select('status', __('ui.status'), ['planned' => __('ui.planned'), 'active' => __('ui.active'), 'closed' => __('ui.closed')], $model, true),
            ]),
            'classes' => self::schoolScoped(SchoolClass::class, 'classes.manage', 'ui.classes', ['grade', 'section'], [
                ['key' => 'display_name', 'label' => __('ui.class')],
                ['key' => 'medium', 'label' => __('ui.medium')],
                ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
            ], fn ($model) => [
                'grade' => ['required', 'string', 'max:20'],
                'section' => ['required', 'string', 'max:10'],
                'medium' => ['required', 'in:tamil,sinhala,english'],
                'academic_year_id' => ['required', 'exists:academic_years,id'],
                'status' => ['required', 'in:active,inactive'],
            ], fn ($user, $model) => [
                self::text('grade', __('ui.grade'), $model, true),
                self::text('section', __('ui.section'), $model, true),
                self::select('medium', __('ui.medium'), ['tamil' => __('ui.tamil'), 'sinhala' => __('ui.sinhala'), 'english' => __('ui.english')], $model, true),
                self::select('academic_year_id', __('ui.academic_year'), AcademicYear::query()->visibleTo($user)->pluck('name', 'id')->all(), $model, true),
                self::select('status', __('ui.status'), $status, $model, true),
            ]),
            'subjects' => self::schoolScoped(Subject::class, 'subjects.manage', 'ui.subjects', ['subject_name', 'subject_code'], [
                ['key' => 'subject_code', 'label' => __('ui.code')],
                ['key' => 'subject_name', 'label' => __('ui.subject')],
                ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
            ], fn ($model) => [
                'subject_code' => ['required', 'string', 'max:20'],
                'subject_name' => ['required', 'string', 'max:255'],
                'medium' => ['required', 'in:tamil,sinhala,english'],
                'status' => ['required', 'in:active,inactive'],
            ], fn ($user, $model) => [
                self::text('subject_code', __('ui.code'), $model, true),
                self::text('subject_name', __('ui.subject'), $model, true),
                self::select('medium', __('ui.medium'), ['tamil' => __('ui.tamil'), 'sinhala' => __('ui.sinhala'), 'english' => __('ui.english')], $model, true),
                self::select('status', __('ui.status'), $status, $model, true),
            ]),
            'students' => self::schoolScoped(\App\Models\Student::class, 'students.manage', 'ui.students', ['full_name', 'admission_number', 'student_identifier'], [
                ['key' => 'admission_number', 'label' => __('ui.admission_number')],
                ['key' => 'full_name', 'label' => __('ui.full_name')],
                ['key' => 'schoolClass.display_name', 'label' => __('ui.class')],
                ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
            ], fn ($model) => [
                'admission_number' => ['required', 'string', 'max:50'],
                'student_identifier' => ['required', 'string', 'max:50', Rule::unique('students', 'student_identifier')->ignore($model?->id)],
                'full_name' => ['required', 'string', 'max:255'],
                'date_of_birth' => ['nullable', 'date'],
                'class_id' => ['nullable', 'exists:classes,id'],
                'status' => ['required', 'in:active,inactive'],
            ], fn ($user, $model) => [
                self::text('admission_number', __('ui.admission_number'), $model, true),
                self::text('student_identifier', __('ui.student_identifier'), $model, true),
                self::text('full_name', __('ui.full_name'), $model, true),
                self::text('date_of_birth', __('ui.date_of_birth'), $model, false, 'date'),
                self::select('class_id', __('ui.class'), SchoolClass::query()->visibleTo($user)->get()->mapWithKeys(fn ($class) => [$class->id => $class->label()])->all(), $model),
                self::select('status', __('ui.status'), $status, $model, true),
            ], ['schoolClass']),
            'parents' => [
                'model' => StudentParent::class,
                'permission' => 'parents.manage',
                'title' => 'ui.parents',
                'parent_scope' => true,
                'search' => ['full_name', 'mobile', 'email'],
                'columns' => [
                    ['key' => 'full_name', 'label' => __('ui.full_name')],
                    ['key' => 'mobile', 'label' => __('ui.mobile')],
                    ['key' => 'preferred_language', 'label' => __('ui.language')],
                ],
                'rules' => fn ($model) => [
                    'full_name' => ['required', 'string', 'max:255'],
                    'mobile' => ['nullable', 'string', 'max:20'],
                    'email' => ['nullable', 'email'],
                    'preferred_language' => ['required', 'in:en,ta,si'],
                    'whatsapp_available' => ['nullable', 'boolean'],
                    'status' => ['required', 'in:active,inactive'],
                ],
                'fields' => fn ($user, $model) => [
                    self::text('full_name', __('ui.full_name'), $model, true),
                    self::text('mobile', __('ui.mobile'), $model),
                    self::text('email', __('ui.email'), $model, false, 'email'),
                    self::select('preferred_language', __('ui.preferred_language'), ['en' => __('ui.english'), 'ta' => __('ui.tamil'), 'si' => __('ui.sinhala')], $model, true),
                    ['name' => 'whatsapp_available', 'label' => __('ui.whatsapp_available'), 'type' => 'checkbox', 'value' => (bool) ($model->whatsapp_available ?? false)],
                    self::select('status', __('ui.status'), $status, $model, true),
                ],
            ],
            'teachers' => self::schoolScoped(\App\Models\Teacher::class, 'teachers.manage', 'ui.teachers', ['full_name', 'employee_identifier'], [
                ['key' => 'employee_identifier', 'label' => __('ui.employee_identifier')],
                ['key' => 'full_name', 'label' => __('ui.full_name')],
                ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
            ], fn ($model) => [
                'employee_identifier' => ['required', 'string', 'max:50'],
                'full_name' => ['required', 'string', 'max:255'],
                'status' => ['required', 'in:active,inactive'],
            ], fn ($user, $model) => [
                self::text('employee_identifier', __('ui.employee_identifier'), $model, true),
                self::text('full_name', __('ui.full_name'), $model, true),
                self::select('status', __('ui.status'), $status, $model, true),
            ]),
            'users' => [
                'model' => User::class,
                'permission' => 'users.manage',
                'title' => 'ui.users',
                'user_scope' => true,
                'with' => ['roles'],
                'search' => ['name', 'email', 'mobile'],
                'columns' => [
                    ['key' => 'name', 'label' => __('ui.name')],
                    ['key' => 'email', 'label' => __('ui.email')],
                    ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
                ],
                'rules' => fn ($model) => [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($model?->id)],
                    'mobile' => ['nullable', 'string', 'max:20'],
                    'status' => ['required', 'in:active,inactive,suspended'],
                    'locale' => ['required', 'in:en,ta,si'],
                    'role' => ['required', 'exists:roles,slug'],
                    'password' => [$model ? 'nullable' : 'required', 'confirmed', PasswordRules::make()],
                    'school_id' => ['nullable', 'exists:schools,id'],
                ],
                'fields' => fn ($user, $model) => [
                    self::text('name', __('ui.name'), $model, true),
                    self::text('email', __('ui.email'), $model, true, 'email'),
                    self::text('mobile', __('ui.mobile'), $model),
                    self::select('role', __('ui.role'), Role::query()->orderBy('name')->pluck('name', 'slug')->all(), $model, true),
                    self::select('school_id', __('ui.school'), School::query()->visibleTo($user)->pluck('school_name', 'id')->all(), $model),
                    self::select('status', __('ui.status'), $status + ['suspended' => __('ui.suspended')], $model, true),
                    self::select('locale', __('ui.language'), ['en' => __('ui.english'), 'ta' => __('ui.tamil'), 'si' => __('ui.sinhala')], $model, true),
                    ['name' => 'password', 'label' => __('ui.password'), 'type' => 'password', 'required' => $model === null],
                    ['name' => 'password_confirmation', 'label' => __('ui.password_confirmation'), 'type' => 'password'],
                ],
            ],
            'terms' => [
                'model' => Term::class,
                'permission' => 'school.admin',
                'title' => 'ui.terms',
                'term_scope' => true,
                'with' => ['academicYear'],
                'search' => ['name'],
                'columns' => [
                    ['key' => 'name', 'label' => __('ui.name')],
                    ['key' => 'academicYear.name', 'label' => __('ui.academic_year')],
                    ['key' => 'status', 'label' => __('ui.status'), 'trans' => true],
                ],
                'rules' => fn ($model) => [
                    'academic_year_id' => ['required', 'exists:academic_years,id'],
                    'name' => ['required', 'string', 'max:50'],
                    'start_date' => ['required', 'date'],
                    'end_date' => ['required', 'date', 'after:start_date'],
                    'status' => ['required', 'in:planned,active,closed'],
                ],
                'fields' => fn ($user, $model) => [
                    self::select('academic_year_id', __('ui.academic_year'), AcademicYear::query()->visibleTo($user)->pluck('name', 'id')->all(), $model, true),
                    self::text('name', __('ui.name'), $model, true),
                    self::text('start_date', __('ui.start_date'), $model, true, 'date'),
                    self::text('end_date', __('ui.end_date'), $model, true, 'date'),
                    self::select('status', __('ui.status'), ['planned' => __('ui.planned'), 'active' => __('ui.active'), 'closed' => __('ui.closed')], $model, true),
                ],
            ],
        ];

        abort_unless(isset($all[$key]), 404);

        return $all[$key];
    }

    private static function schoolScoped(string $model, string $permission, string $title, array $search, array $columns, \Closure $rules, \Closure $fields, array $with = []): array
    {
        return [
            'model' => $model,
            'permission' => $permission,
            'title' => $title,
            'school' => true,
            'with' => $with,
            'search' => $search,
            'columns' => $columns,
            'rules' => $rules,
            'fields' => $fields,
        ];
    }

    private static function text(string $name, string $label, $model, bool $required = false, string $type = 'text'): array
    {
        $value = $model?->{$name};
        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        return ['name' => $name, 'label' => $label, 'type' => $type, 'required' => $required, 'value' => $value];
    }

    private static function select(string $name, string $label, array $options, $model, bool $required = false): array
    {
        $value = $model?->{$name};
        if ($name === 'role') {
            $value = $model?->roles?->first()?->slug;
        }

        return ['name' => $name, 'label' => $label, 'type' => 'select', 'options' => $options, 'required' => $required, 'value' => $value];
    }
}
