<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialBalanceCutoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'cutoff_number', 'year', 'cutoff_date', 'label', 'cash_bank', 'receivables', 'inventory',
        'fixed_assets_gross', 'accumulated_depreciation', 'fixed_assets_net', 'total_assets',
        'payables', 'owner_equity', 'current_year_profit', 'total_liabilities', 'total_equity',
        'created_by',
    ];

    protected $casts = [
        'cutoff_date' => 'date',
        'cash_bank' => 'decimal:2',
        'receivables' => 'decimal:2',
        'inventory' => 'decimal:2',
        'fixed_assets_gross' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'fixed_assets_net' => 'decimal:2',
        'total_assets' => 'decimal:2',
        'payables' => 'decimal:2',
        'owner_equity' => 'decimal:2',
        'current_year_profit' => 'decimal:2',
        'total_liabilities' => 'decimal:2',
        'total_equity' => 'decimal:2',
    ];
}
