<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'material_category_id', 'sku', 'name', 'price', 'cost_price', 'min_stock', 'binrow', 'photo_path', 'is_active',
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

    public function adjustments(): HasMany
    {
        return $this->hasMany(MaterialStockAdjustment::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('r2')->url($this->photo_path) : null;
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
