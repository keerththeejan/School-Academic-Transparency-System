<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = ['school_id', 'scope_key', 'key', 'value', 'type'];
}
