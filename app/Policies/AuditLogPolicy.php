<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function view(User $user, AuditLog $log): bool
    {
        return $user->hasPermission('audit.view')
            && app(\App\Services\SchoolAccess::class)->canAccessSchool($user, $log->school_id);
    }

    public function update(User $user, AuditLog $log): bool
    {
        return false;
    }

    public function delete(User $user, AuditLog $log): bool
    {
        return false;
    }
}
