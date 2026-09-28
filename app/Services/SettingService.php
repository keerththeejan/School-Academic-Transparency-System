<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Schema;

class SettingService
{
    public function get(string $key, mixed $default = null, ?int $schoolId = null): mixed
    {
        if (! $this->ready()) {
            return $default;
        }

        if ($schoolId) {
            $school = SystemSetting::query()
                ->where('scope_key', 'school:'.$schoolId)
                ->where('key', $key)
                ->first();
            if ($school) {
                return $this->cast($school);
            }
        }

        $global = SystemSetting::query()
            ->where('scope_key', 'global')
            ->where('key', $key)
            ->first();

        return $global ? $this->cast($global) : $default;
    }

    public function set(string $key, mixed $value, ?int $schoolId = null, string $type = 'string'): void
    {
        $scope = $schoolId ? 'school:'.$schoolId : 'global';
        SystemSetting::query()->updateOrCreate(
            ['scope_key' => $scope, 'key' => $key],
            [
                'school_id' => $schoolId,
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'type' => $type,
            ]
        );
    }

    private function cast(SystemSetting $setting): mixed
    {
        return match ($setting->type) {
            'bool', 'boolean' => in_array($setting->value, ['1', 'true', 'yes'], true),
            'int', 'integer' => (int) $setting->value,
            'json' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }

    private function ready(): bool
    {
        try {
            return Schema::hasTable('system_settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
