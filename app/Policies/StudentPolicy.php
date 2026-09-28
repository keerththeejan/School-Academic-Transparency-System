<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use App\Services\SchoolAccess;

class StudentPolicy
{
    public function view(User $user, Student $student): bool
    {
        if ($user->hasPermission('students.manage')) {
            return app(SchoolAccess::class)->canAccessSchool($user, (int) $student->school_id);
        }

        if ($user->hasPermission('children.view.own')) {
            return (bool) $user->parentProfile?->students()->where('students.id', $student->id)->exists();
        }

        return false;
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasPermission('students.manage')
            && app(SchoolAccess::class)->canAccessSchool($user, (int) $student->school_id);
    }
}
