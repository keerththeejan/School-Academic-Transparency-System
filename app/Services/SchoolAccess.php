<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SchoolAccess
{
    public function allowsAll(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'ministry_admin']);
    }

    /**
     * @return array<int>|null Null means every school.
     */
    public function allowedSchoolIds(User $user): ?array
    {
        if ($this->allowsAll($user)) {
            return null;
        }

        if ($user->hasRole('provincial_admin')) {
            if (! $user->province_id) {
                return [];
            }

            return School::query()->where('province_id', $user->province_id)->pluck('id')->all();
        }

        if ($user->hasRole('zonal_admin')) {
            if (! $user->zone_id) {
                return [];
            }

            return School::query()->where('zone_id', $user->zone_id)->pluck('id')->all();
        }

        return $user->school_id ? [(int) $user->school_id] : [];
    }

    public function constrain(Builder $query, User $user, string $column = 'school_id'): Builder
    {
        $ids = $this->allowedSchoolIds($user);

        if ($ids === null) {
            return $query;
        }

        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn($column, $ids);
    }

    public function canAccessSchool(User $user, ?int $schoolId): bool
    {
        if ($schoolId === null) {
            return $this->allowsAll($user);
        }

        $ids = $this->allowedSchoolIds($user);

        return $ids === null || in_array($schoolId, $ids, true);
    }
}
