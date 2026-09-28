<?php

namespace App\Http\Controllers;

use App\Services\SchoolAccess;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    protected function schoolId(Request $request): int
    {
        $user = $request->user();
        $requested = $request->integer('school_id') ?: null;
        $schoolId = $user->school_id && ! $user->hasAnyRole(['super_admin', 'ministry_admin', 'provincial_admin', 'zonal_admin'])
            ? (int) $user->school_id
            : (int) ($requested ?: $user->school_id);

        abort_unless($schoolId && app(SchoolAccess::class)->canAccessSchool($user, $schoolId), 403);

        return $schoolId;
    }
}
