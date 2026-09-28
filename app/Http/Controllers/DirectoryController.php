<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentParent;
use App\Models\Term;
use App\Services\AuditLogger;
use App\Services\SchoolAccess;
use App\Support\DirectoryRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirectoryController extends Controller
{
    public function index(Request $request)
    {
        $key = $request->route('directory');
        $def = $this->definition($request, $key, true);
        $model = $def['model'];
        $query = $model::query();
        $this->scope($query, $request, $def);

        if ($request->filled('q')) {
            $term = addcslashes($request->string('q')->toString(), '%_');
            $query->where(function ($inner) use ($def, $term) {
                foreach ($def['search'] as $column) {
                    $inner->orWhere($column, 'like', '%'.$term.'%');
                }
            });
        }

        if (! empty($def['with'])) {
            $query->with($def['with']);
        }

        return view('admin.index', [
            'title' => __($def['title']),
            'records' => $query->latest('id')->paginate(20)->withQueryString(),
            'columns' => $def['columns'],
            'createUrl' => $request->user()->hasPermission($def['permission']) ? route($key.'.create') : null,
            'editRoute' => $request->user()->hasPermission($def['permission']) ? $key.'.edit' : null,
        ]);
    }

    public function create(Request $request)
    {
        $key = $request->route('directory');
        $def = $this->definition($request, $key);

        return view('admin.form', [
            'title' => __('ui.create').' — '.__($def['title']),
            'action' => route($key.'.store'),
            'method' => 'POST',
            'back' => route($key.'.index'),
            'fields' => $def['fields']($request->user(), null),
        ]);
    }

    public function store(Request $request, AuditLogger $audit)
    {
        $key = $request->route('directory');
        $def = $this->definition($request, $key);
        $data = $request->validate($def['rules'](null));
        $data = $this->prepare($request, $def, $data);

        $record = DB::transaction(function () use ($def, $data, $request, $audit) {
            $model = $def['model'];
            $record = $model::query()->create($data);
            $this->afterSave($request, $record, $data);
            $audit->log($request->user(), 'CREATE', $record->getTable(), $record->id, null, $record->only(array_keys($record->getAttributes())), null, $record->school_id ?? $request->user()->school_id);

            return $record;
        });

        return redirect()->route($key.'.index')->with('status', __('ui.created'));
    }

    public function edit(Request $request, int $record)
    {
        $key = $request->route('directory');
        $def = $this->definition($request, $key);
        $model = $this->find($request, $def, $record);

        return view('admin.form', [
            'title' => __('ui.edit').' — '.__($def['title']),
            'action' => route($key.'.update', $model->id),
            'method' => 'PUT',
            'back' => route($key.'.index'),
            'fields' => $def['fields']($request->user(), $model),
        ]);
    }

    public function update(Request $request, int $record, AuditLogger $audit)
    {
        $key = $request->route('directory');
        $def = $this->definition($request, $key);
        $model = $this->find($request, $def, $record);
        $data = $request->validate($def['rules']($model));
        $data = $this->prepare($request, $def, $data, $model);
        $old = $model->only(array_keys($data));

        DB::transaction(function () use ($model, $data, $request, $audit, $old) {
            $model->fill($data)->save();
            $this->afterSave($request, $model, $data);
            $audit->log($request->user(), 'UPDATE', $model->getTable(), $model->id, $old, $model->only(array_keys($old)), null, $model->school_id ?? $request->user()->school_id);
        });

        return redirect()->route($key.'.index')->with('status', __('ui.updated'));
    }

    private function definition(Request $request, string $key, bool $view = false): array
    {
        $def = DirectoryRegistry::get($key);
        $permission = $view ? ($def['view_permission'] ?? $def['permission']) : $def['permission'];
        abort_unless($request->user()->hasPermission($permission), 403);

        return $def;
    }

    private function scope($query, Request $request, array $def): void
    {
        $user = $request->user();
        if (! empty($def['school'])) {
            $query->visibleTo($user);
        }
        if (! empty($def['school_column'])) {
            app(SchoolAccess::class)->constrain($query, $user, 'schools.id');
        }
        if (! empty($def['user_scope'])) {
            app(SchoolAccess::class)->constrain($query, $user, 'users.school_id');
        }
        if (! empty($def['parent_scope']) && $user->school_id && ! $user->hasAnyRole(['super_admin', 'ministry_admin'])) {
            $ids = app(SchoolAccess::class)->allowedSchoolIds($user) ?? [];
            $query->whereHas('students', fn ($students) => $students->whereIn('school_id', $ids ?: [-1]));
        }
        if (! empty($def['term_scope'])) {
            $query->whereHas('academicYear', fn ($year) => $year->visibleTo($user));
        }
    }

    private function find(Request $request, array $def, int $id)
    {
        $query = $def['model']::query();
        $this->scope($query, $request, $def);

        return $query->findOrFail($id);
    }

    private function prepare(Request $request, array $def, array $data, $model = null): array
    {
        unset($data['role'], $data['password_confirmation']);
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }
        if (! empty($def['school']) && empty($data['school_id'])) {
            $data['school_id'] = $this->schoolId($request);
        }
        if (! empty($data['school_id'])) {
            abort_unless(app(SchoolAccess::class)->canAccessSchool($request->user(), (int) $data['school_id']), 403);
        }
        if (($def['model'] ?? null) === School::class && $request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        } else {
            unset($data['logo']);
        }
        if (($def['model'] ?? null) === \App\Models\StudentParent::class) {
            $data['whatsapp_available'] = $request->boolean('whatsapp_available');
        }
        if (! empty($data['class_id'])) {
            $class = \App\Models\SchoolClass::query()->findOrFail($data['class_id']);
            abort_unless(app(SchoolAccess::class)->canAccessSchool($request->user(), (int) $class->school_id), 403);
            $data['school_id'] = $class->school_id;
        }
        if (! empty($data['academic_year_id']) && ($def['model'] ?? null) === \App\Models\SchoolClass::class) {
            $year = AcademicYear::query()->findOrFail($data['academic_year_id']);
            abort_unless((int) $year->school_id === (int) ($data['school_id'] ?? $model?->school_id), 403);
        }
        if (($def['model'] ?? null) === AcademicYear::class && ($data['status'] ?? null) === 'active') {
            AcademicYear::query()->where('school_id', $data['school_id'] ?? $model?->school_id)->update(['status' => 'closed']);
        }

        return $data;
    }

    private function afterSave(Request $request, $record, array $data): void
    {
        if ($record instanceof \App\Models\User && $request->filled('role')) {
            $role = Role::query()->where('slug', $request->string('role'))->firstOrFail();
            $record->roles()->sync([$role->id]);
        }

        if ($record instanceof Student && $request->filled('parent_id')) {
            $parent = StudentParent::query()->findOrFail($request->integer('parent_id'));
            abort_unless(app(SchoolAccess::class)->canAccessSchool($request->user(), (int) $record->school_id), 403);
            $record->parents()->syncWithoutDetaching([
                $parent->id => ['relationship' => 'parent', 'is_primary' => true],
            ]);
        }

        if ($record instanceof Term) {
            $year = AcademicYear::query()->findOrFail($record->academic_year_id);
            abort_unless(app(SchoolAccess::class)->canAccessSchool($request->user(), (int) $year->school_id), 403);
        }
    }
}
