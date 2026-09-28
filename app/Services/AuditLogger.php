<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public function log(
        ?User $user,
        string $action,
        string $recordType,
        ?int $recordId = null,
        mixed $old = null,
        mixed $new = null,
        ?string $reason = null,
        ?int $schoolId = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'user_id' => $user?->id,
            'school_id' => $schoolId ?? $user?->school_id,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'old_value' => $old,
            'new_value' => $new,
            'reason' => $reason,
            'ip_address' => Request::ip(),
            'user_agent' => mb_substr((string) Request::userAgent(), 0, 2000),
            'timestamp' => now(),
        ]);
    }
}
