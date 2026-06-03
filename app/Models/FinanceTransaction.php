<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number', 'transaction_date', 'finance_item_id', 'bank_account_id',
        'description', 'amount', 'notes', 'created_by',
    ];

    protected $casts = ['transaction_date' => 'date', 'amount' => 'decimal:2'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(FinanceItem::class, 'finance_item_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
