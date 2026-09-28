<?php

namespace App\Services;

use App\Models\TimetableVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimetableService
{
    public function __construct(private AuditLogger $audit) {}

    public function approve(User $actor, TimetableVersion $version): TimetableVersion
    {
        if ($version->entries()->count() === 0) {
            throw ValidationException::withMessages([
                'version' => __('ui.timetable_needs_entries'),
            ]);
        }

        return DB::transaction(function () use ($actor, $version) {
            $old = $version->only(['status', 'approved_by', 'approved_at']);
            $version->forceFill([
                'status' => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ])->save();

            $this->audit->log($actor, 'APPROVE', 'timetable_version', $version->id, $old, $version->only(['status', 'approved_by', 'approved_at']), null, $version->school_id);

            return $version;
        });
    }

    public function publish(User $actor, TimetableVersion $version): TimetableVersion
    {
        if (! in_array($version->status, ['approved', 'published'], true)) {
            throw ValidationException::withMessages([
                'version' => __('ui.timetable_must_be_approved'),
            ]);
        }

        return DB::transaction(function () use ($actor, $version) {
            $overlapping = TimetableVersion::query()
                ->where('school_id', $version->school_id)
                ->where('id', '!=', $version->id)
                ->where('status', 'published')
                ->lockForUpdate()
                ->get();

            foreach ($overlapping as $oldVersion) {
                $before = $oldVersion->only(['status', 'effective_to']);
                if ($oldVersion->effective_to === null || $oldVersion->effective_to->gte($version->effective_from)) {
                    $oldVersion->effective_to = $version->effective_from->copy()->subDay();
                }
                if ($oldVersion->effective_to && $oldVersion->effective_to->lt($version->effective_from)) {
                    $oldVersion->status = 'archived';
                }
                $oldVersion->save();
                $this->audit->log(
                    $actor,
                    'UPDATE',
                    'timetable_version',
                    $oldVersion->id,
                    $before,
                    $oldVersion->only(['status', 'effective_to']),
                    'Superseded by timetable version '.$version->version_number.'. Historical periods were kept.',
                    $oldVersion->school_id
                );
            }

            $before = $version->only(['status', 'published_at']);
            $version->forceFill([
                'status' => 'published',
                'published_at' => now(),
            ])->save();

            $this->audit->log($actor, 'PUBLISH', 'timetable_version', $version->id, $before, $version->only(['status', 'published_at']), null, $version->school_id);

            return $version->refresh();
        });
    }
}
