<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = ['task_id', 'title', 'is_checked', 'position'];

    protected $casts = ['is_checked' => 'boolean'];
}
