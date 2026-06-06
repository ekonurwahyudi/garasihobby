<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_number', 'asset_name', 'asset_category', 'asset_category_id', 'purchase_date', 'supplier', 'serial_number', 'condition_status',
        'purchase_amount', 'useful_life_years', 'residual_value', 'depreciation_method',
        'depreciation_percentage', 'book_value', 'bank_account_id', 'asset_photo_paths',
        'evidence_paths', 'notes', 'status', 'submitted_by', 'submitted_at', 'approved_by',
        'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason', 'finance_transaction_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_amount' => 'decimal:2',
        'residual_value' => 'decimal:2',
        'depreciation_percentage' => 'decimal:2',
        'book_value' => 'decimal:2',
        'asset_photo_paths' => 'array',
        'evidence_paths' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function bankAccount(): BelongsTo { return $this->belongsTo(BankAccount::class); }
    public function category(): BelongsTo { return $this->belongsTo(AssetCategory::class, 'asset_category_id'); }
    public function financeTransaction(): BelongsTo { return $this->belongsTo(FinanceTransaction::class); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function rejecter(): BelongsTo { return $this->belongsTo(User::class, 'rejected_by'); }
}
