<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DebtReceivable extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number', 'type', 'transaction_date', 'due_date', 'party_name', 'category', 'debt_receivable_category_id', 'bank_account_id',
        'activity', 'amount', 'total_amount', 'paid_amount', 'remaining_amount', 'payment_status',
        'status', 'notes', 'evidence_paths', 'submitted_by', 'submitted_at', 'approved_by',
        'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'evidence_paths' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function payments(): HasMany { return $this->hasMany(DebtReceivablePayment::class); }
    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
    public function categoryMaster(): BelongsTo { return $this->belongsTo(DebtReceivableCategory::class, 'debt_receivable_category_id'); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function rejecter(): BelongsTo { return $this->belongsTo(User::class, 'rejected_by'); }
}
