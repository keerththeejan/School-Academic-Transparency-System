<?php

return [
    'locales' => ['en', 'ta', 'si'],
    'summary_time' => env('SATS_SUMMARY_TIME', '15:30'),
    'period_duration_minutes' => 40,
    'password_min_length' => 8,
    'session_timeout' => (int) env('SESSION_LIFETIME', 120),
    'notification_retries' => 3,
    'attention_threshold' => 3,
];
