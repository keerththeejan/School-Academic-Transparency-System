<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'status',
        'last_login_at',
        'email_verified_at',
        'locale',
        'otp_enabled',
        'otp_secret',
        'province_id',
        'district_id',
        'zone_id',
        'school_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'otp_enabled' => 'boolean',
            'otp_secret' => 'encrypted',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function parentProfile(): HasOne
    {
        return $this->hasOne(StudentParent::class);
    }

    public function teacherProfile(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function portalNotifications(): HasMany
    {
        return $this->hasMany(PortalNotification::class);
    }

    public function hasRole(string $slug): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains('slug', $slug);
    }

    public function hasAnyRole(array $slugs): bool
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('slug')->intersect($slugs)->isNotEmpty();
    }

    public function hasPermission(string $slug): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        $this->loadMissing('roles.permissions');

        return $this->roles->flatMap->permissions->contains('slug', $slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        foreach ($slugs as $slug) {
            if ($this->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }

    public function primaryRole(): ?string
    {
        $order = [
            'super_admin',
            'ministry_admin',
            'provincial_admin',
            'zonal_admin',
            'principal',
            'academic_coordinator',
            'school_officer',
            'sdc_viewer',
            'teacher',
            'parent',
        ];

        foreach ($order as $role) {
            if ($this->hasRole($role)) {
                return $role;
            }
        }

        return $this->roles->first()?->slug;
    }

    public function seesTeacherIdentity(): bool
    {
        return $this->hasAnyRole([
            'super_admin',
            'principal',
            'academic_coordinator',
            'school_officer',
            'teacher',
        ]);
    }

    public function isAggregateViewer(): bool
    {
        return $this->hasAnyRole([
            'ministry_admin',
            'provincial_admin',
            'zonal_admin',
            'sdc_viewer',
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
