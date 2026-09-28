<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        abort_unless(request()->user()->hasPermission('roles.manage'), 403);

        return view('roles.index', ['roles' => Role::query()->withCount('permissions')->orderBy('name')->get()]);
    }

    public function edit(int $role)
    {
        abort_unless(request()->user()->hasPermission('roles.manage'), 403);
        $record = Role::query()->with('permissions')->findOrFail($role);

        return view('roles.edit', [
            'role' => $record,
            'permissions' => Permission::query()->orderBy('group')->orderBy('slug')->get(),
        ]);
    }

    public function update(Request $request, int $role, AuditLogger $audit)
    {
        abort_unless($request->user()->hasPermission('permissions.manage'), 403);
        $record = Role::query()->findOrFail($role);
        $ids = $request->input('permissions', []);
        $old = $record->permissions()->pluck('slug');
        $record->permissions()->sync($ids);
        $audit->log($request->user(), 'UPDATE', 'role', $record->id, ['permissions' => $old], ['permissions' => $record->permissions()->pluck('slug')], null, null);

        return back()->with('status', __('ui.saved'));
    }
}
