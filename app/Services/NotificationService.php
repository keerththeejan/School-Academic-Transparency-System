<?php

namespace App\Services;

use App\Jobs\SendNotificationJob;
use App\Models\DailySummary;
use App\Models\DiscrepancyCase;
use App\Models\NotificationLog;
use App\Models\PortalNotification;
use App\Models\StudentParent;
use App\Models\User;

class NotificationService
{
    public function __construct(private SummaryWording $wording) {}

    public function notifySummary(DailySummary $summary): void
    {
        $summary->loadMissing(['school', 'schoolClass', 'items']);
        $periods = $summary->items->count();
        $deviations = $summary->items->where('status', '!=', 'no_deviation')->count();

        $parents = StudentParent::query()
            ->with('user')
            ->where('status', 'active')
            ->whereHas('students', function ($query) use ($summary) {
                $query->where('students.class_id', $summary->class_id)->where('students.status', 'active');
            })
            ->get();

        foreach ($parents as $parent) {
            $locale = in_array($parent->preferred_language, config('sats.locales'), true)
                ? $parent->preferred_language
                : 'en';
            $previous = app()->getLocale();
            app()->setLocale($locale);
            $title = __('ui.notification_title');
            $body = __('ui.notification_body', [
                'school' => $summary->school->school_name,
                'class' => $summary->schoolClass->label(),
                'date' => $summary->date->translatedFormat('d F Y'),
                'periods' => $periods,
                'deviations' => $deviations,
            ]);
            app()->setLocale($previous);

            $this->sendParent($parent, (int) $summary->school_id, 'daily_summary', $title, $body);
        }
    }

    public function notifyReviewers(DiscrepancyCase $case): void
    {
        $case->loadMissing('feedback.student', 'school');
        $reviewers = User::query()
            ->where('school_id', $case->school_id)
            ->where('status', 'active')
            ->whereHas('roles.permissions', fn ($query) => $query->where('slug', 'discrepancies.review'))
            ->get();

        foreach ($reviewers as $reviewer) {
            PortalNotification::query()->create([
                'school_id' => $case->school_id,
                'user_id' => $reviewer->id,
                'notification_type' => 'discrepancy_opened',
                'title' => 'discrepancy_opened',
                'body' => __('ui.discrepancy_alert', [
                    'class' => $case->feedback?->student?->schoolClass?->label() ?? '',
                    'date' => optional($case->feedback?->date)->toDateString(),
                ]),
                'data' => ['case_id' => $case->id],
            ]);
        }
    }

    public function sendParent(StudentParent $parent, int $schoolId, string $type, string $title, string $body): void
    {
        $this->writePortal($parent, $schoolId, $type, $title, $body);

        if ($parent->email) {
            $this->queueExternal($parent, $schoolId, 'email', $type, $parent->email, $title."\n".$body);
        }

        $sms = app(SettingService::class)->get('sms_enabled', false, $schoolId);
        $whatsapp = app(SettingService::class)->get('whatsapp_enabled', false, $schoolId);

        if ($sms && $parent->mobile) {
            $this->queueExternal($parent, $schoolId, 'sms', $type, $parent->mobile, $body);
        }

        if ($whatsapp && $parent->whatsapp_available && $parent->mobile) {
            $this->queueExternal($parent, $schoolId, 'whatsapp', $type, $parent->mobile, $body);
        }
    }

    public function retryFailed(): int
    {
        $limit = (int) config('sats.notification_retries');
        $logs = NotificationLog::query()
            ->where('status', 'failed')
            ->where('attempts', '<', $limit)
            ->limit(100)
            ->get();

        foreach ($logs as $log) {
            $log->forceFill(['status' => 'pending', 'error_message' => null])->save();
            SendNotificationJob::dispatch($log->id);
        }

        return $logs->count();
    }

    private function writePortal(StudentParent $parent, int $schoolId, string $type, string $title, string $body): void
    {
        PortalNotification::query()->create([
            'school_id' => $schoolId,
            'user_id' => $parent->user_id,
            'parent_id' => $parent->id,
            'notification_type' => $type,
            'title' => $title,
            'body' => $body,
        ]);

        NotificationLog::query()->create([
            'school_id' => $schoolId,
            'parent_id' => $parent->id,
            'channel' => 'portal',
            'notification_type' => $type,
            'recipient' => $parent->user?->email ?? $parent->email,
            'message' => $body,
            'status' => 'sent',
            'provider_message_id' => 'portal',
            'sent_at' => now(),
            'attempts' => 1,
        ]);
    }

    private function queueExternal(StudentParent $parent, int $schoolId, string $channel, string $type, string $recipient, string $message): void
    {
        $log = NotificationLog::query()->create([
            'school_id' => $schoolId,
            'parent_id' => $parent->id,
            'channel' => $channel,
            'notification_type' => $type,
            'recipient' => $recipient,
            'message' => $message,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        SendNotificationJob::dispatch($log->id);
    }
}
