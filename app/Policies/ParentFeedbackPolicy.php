<?php

namespace App\Policies;

use App\Models\ParentFeedback;
use App\Models\User;

class ParentFeedbackPolicy
{
    public function view(User $user, ParentFeedback $feedback): bool
    {
        if ($user->hasPermission('discrepancies.report.own')) {
            return (int) $user->parentProfile?->id === (int) $feedback->parent_id;
        }

        if ($user->hasPermission('discrepancies.review') || $user->hasPermission('feedback.view')) {
            return app(\App\Services\SchoolAccess::class)->canAccessSchool($user, (int) $feedback->school_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('discrepancies.report.own');
    }
}
