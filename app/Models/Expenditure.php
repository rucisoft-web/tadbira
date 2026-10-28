<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expenditure extends Model
{
    protected $fillable = ['budget_line_id', 'status', 'amount', 'transaction_date', 'reference', 'recorded_by'];

    protected $casts = ['amount' => 'float', 'transaction_date' => 'date:Y-m-d'];

    public function budgetLine(): BelongsTo
    {
        return $this->belongsTo(BudgetLine::class);
    }
}
