<?php

namespace App\Support;

class DeviationCatalog
{
    public const NOT_CONDUCTED = ['lesson_not_conducted', 'relief_unavailable'];

    public static function grouped(): array
    {
        return [
            'teacher' => [
                'approved_leave',
                'official_duty',
                'training',
                'examination_duty',
                'meeting',
                'emergency_absence',
            ],
            'timetable' => [
                'period_changed',
                'teacher_substituted',
                'class_combined',
                'subject_changed',
            ],
            'class' => [
                'school_activity',
                'examination',
                'sports_activity',
                'assembly',
                'special_program',
                'holiday',
                'substitute_holiday',
            ],
            'delivery' => [
                'lesson_not_conducted',
                'lesson_partially_conducted',
                'relief_unavailable',
            ],
            'other' => ['other'],
        ];
    }

    public static function keys(): array
    {
        return array_merge(...array_values(self::grouped()));
    }

    public static function absenceTypes(): array
    {
        return self::grouped()['teacher'];
    }
}
