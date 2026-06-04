<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankBalanceAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_account_id', 'previous_balance', 'new_balance', 'difference', 'type', 'description', 'created_by',
    ];

    protected $casts = [
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
