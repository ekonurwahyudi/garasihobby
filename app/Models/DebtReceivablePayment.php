<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtReceivablePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_receivable_id', 'payment_date', 'amount', 'bank_account_id', 'notes',
        'evidence_paths', 'finance_transaction_id', 'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'evidence_paths' => 'array',
    ];

    public function debtReceivable(): BelongsTo { return $this->belongsTo(DebtReceivable::class); }
    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
    public function financeTransaction(): BelongsTo { return $this->belongsTo(FinanceTransaction::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
