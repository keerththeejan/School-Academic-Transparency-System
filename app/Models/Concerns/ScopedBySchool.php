<?php

namespace App\Models\Concerns;

use App\Models\User;
use App\Services\SchoolAccess;
use Illuminate\Database\Eloquent\Builder;

trait ScopedBySchool
{
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return app(SchoolAccess::class)->constrain($query, $user, $query->getModel()->getTable().'.school_id');
    }
}
