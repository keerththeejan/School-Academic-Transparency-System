<?php

namespace App\Models;

use App\Exceptions\AuditImmutableException;
use App\Models\Concerns\ScopedBySchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use ScopedBySchool;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'school_id',
        'action',
        'record_type',
        'record_id',
        'old_value',
        'new_value',
        'reason',
        'ip_address',
        'user_agent',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'old_value' => 'array',
            'new_value' => 'array',
            'timestamp' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new AuditImmutableException('Audit logs are append-only.');
        });

        static::deleting(function () {
            throw new AuditImmutableException('Audit logs cannot be deleted.');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
