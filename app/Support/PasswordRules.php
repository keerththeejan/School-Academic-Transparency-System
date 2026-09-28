<?php

namespace App\Support;

use App\Services\SettingService;
use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    public static function make(): Password
    {
        $settings = app(SettingService::class);
        $rule = Password::min((int) $settings->get('password_min_length', config('sats.password_min_length')));

        if ($settings->get('password_require_mixed', true)) {
            $rule = $rule->mixedCase();
        }

        if ($settings->get('password_require_numbers', true)) {
            $rule = $rule->numbers();
        }

        return $rule;
    }
}
