<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    public $timestamps = false;

    protected $fillable = ['organization_id', 'date', 'name', 'is_working_day'];

    protected $casts = ['date' => 'date:Y-m-d', 'is_working_day' => 'boolean'];
}
