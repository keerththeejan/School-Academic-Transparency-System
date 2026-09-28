<?php

namespace App\Support;

class CalendarTypes
{
    public static function all(): array
    {
        return [
            'school_holiday',
            'public_holiday',
            'term_holiday',
            'substitute_school_day',
            'special_activity',
            'examination',
            'sports_event',
            'teacher_training',
            'unexpected_closure',
        ];
    }
}
