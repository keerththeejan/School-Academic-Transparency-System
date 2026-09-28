<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class SqlDate
{
    public static function month(string $column): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }
}
