<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'material_category_id', 'sku', 'name', 'price', 'cost_price', 'min_stock', 'binrow', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function stock(): HasOne
    {
        return $this->hasOne(MaterialStock::class);
    }

    public function getStockQtyAttribute(): int
    {
        return $this->stock?->qty ?? 0;
    }

    public function getStockStatusAttribute(): string
    {
        $qty = $this->stock_qty;
        if ($qty === 0) return 'Habis';
        if ($qty <= $this->min_stock) return 'Hampir Habis';
        return 'Aman';
    }
}
