<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RevenueCutoff extends Model
{
    use HasFactory;

    protected $fillable = [
        'cutoff_number', 'cutoff_type', 'cutoff_year', 'cutoff_month', 'cutoff_quarter',
        'period_start', 'period_end', 'gross_revenue', 'total_expense', 'net_revenue', 'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_revenue' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_revenue' => 'decimal:2',
    ];

    public function sharings(): HasMany
    {
        return $this->hasMany(RevenueSharing::class);
    }

    public function activeSharings(): HasMany
    {
        return $this->hasMany(RevenueSharing::class)->where('status', '!=', 'ditolak');
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
