<?php

namespace App\Support;

class Classifications
{
    public static function all(): array
    {
        return [
            'school_record_confirmed',
            'school_record_incomplete',
            'parent_information_incorrect',
            'unable_to_verify',
            'repeated_discrepancy',
        ];
    }
}
