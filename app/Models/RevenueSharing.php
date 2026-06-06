<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueSharing extends Model
{
    use HasFactory;

    protected $fillable = [
        'revenue_cutoff_id', 'sharing_number', 'recipient_name', 'cutoff_type', 'cutoff_year', 'cutoff_month', 'cutoff_quarter',
        'period_start', 'period_end', 'gross_revenue', 'total_expense', 'net_revenue',
        'sharing_percentage', 'sharing_amount', 'bank_account_id', 'evidence_paths', 'notes',
        'status', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at',
        'rejected_by', 'rejected_at', 'rejection_reason', 'finance_transaction_id',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_revenue' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_revenue' => 'decimal:2',
        'sharing_percentage' => 'decimal:2',
        'sharing_amount' => 'decimal:2',
        'evidence_paths' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function revenueCutoff(): BelongsTo
    {
        return $this->belongsTo(RevenueCutoff::class);
    }

    public function financeTransaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function getPeriodLabelAttribute(): string
    {
        return match ($this->cutoff_type) {
            'monthly' => $this->period_start?->translatedFormat('F Y') ?? '-',
            'quarterly' => 'Triwulan ' . $this->cutoff_quarter . ' ' . $this->cutoff_year,
            default => 'Tahun ' . $this->cutoff_year,
        };
    }
}
