<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit(Request $request, SettingService $settings)
    {
        abort_unless($request->user()->hasPermission('settings.manage'), 403);
        $schoolId = $request->user()->hasRole('super_admin') ? null : $request->user()->school_id;
        $definitions = [
            'timezone' => ['label' => __('ui.timezone'), 'type' => 'string', 'default' => config('app.timezone')],
            'default_language' => ['label' => __('ui.language'), 'type' => 'string', 'default' => 'en'],
            'school_start_time' => ['label' => __('ui.start_time'), 'type' => 'string', 'default' => '07:30'],
            'school_end_time' => ['label' => __('ui.end_time'), 'type' => 'string', 'default' => '13:30'],
            'daily_summary_generation_time' => ['label' => __('ui.summary_time'), 'type' => 'string', 'default' => config('sats.summary_time')],
            'period_duration_minutes' => ['label' => __('ui.period'), 'type' => 'int', 'default' => 40],
            'session_timeout_minutes' => ['label' => __('ui.session_timeout'), 'type' => 'int', 'default' => 120],
            'password_min_length' => ['label' => __('ui.password'), 'type' => 'int', 'default' => 8],
            'sms_enabled' => ['label' => __('ui.sms_enabled'), 'type' => 'bool', 'default' => false],
            'whatsapp_enabled' => ['label' => __('ui.whatsapp_enabled'), 'type' => 'bool', 'default' => false],
        ];
        $fields = [];
        foreach ($definitions as $key => $meta) {
            $fields[$key] = $meta + ['value' => $settings->get($key, $meta['default'], $schoolId)];
        }

        return view('settings.edit', compact('fields'));
    }

    public function update(Request $request, SettingService $settings, AuditLogger $audit)
    {
        abort_unless($request->user()->hasPermission('settings.manage'), 403);
        $schoolId = $request->user()->hasRole('super_admin') ? null : $request->user()->school_id;
        $data = $request->validate([
            'timezone' => ['required', 'string', 'max:64'],
            'default_language' => ['required', 'in:en,ta,si'],
            'school_start_time' => ['required', 'date_format:H:i'],
            'school_end_time' => ['required', 'date_format:H:i'],
            'daily_summary_generation_time' => ['required', 'date_format:H:i'],
            'period_duration_minutes' => ['required', 'integer', 'min:20', 'max:90'],
            'session_timeout_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'password_min_length' => ['required', 'integer', 'min:8', 'max:64'],
            'sms_enabled' => ['required', 'boolean'],
            'whatsapp_enabled' => ['required', 'boolean'],
        ]);

        foreach ($data as $key => $value) {
            $type = in_array($key, ['sms_enabled', 'whatsapp_enabled'], true) ? 'bool' : (is_int($value) ? 'int' : 'string');
            $settings->set($key, $value, $schoolId, $type);
        }
        $audit->log($request->user(), 'UPDATE', 'system_settings', null, null, $data, null, $schoolId);

        return back()->with('status', __('ui.saved'));
    }
}
