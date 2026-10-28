<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetLine extends Model
{
    protected $fillable = [
        'project_id', 'fiscal_year_id', 'parent_id', 'task_id', 'level', 'code', 'description',
        'volume', 'unit', 'unit_price', 'amount', 'funding_source', 'position',
    ];

    protected $casts = [
        'volume' => 'float',
        'unit_price' => 'float',
        'amount' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(BudgetLine::class, 'parent_id')->orderBy('position');
    }

    public function expenditures(): HasMany
    {
        return $this->hasMany(Expenditure::class);
    }
}
