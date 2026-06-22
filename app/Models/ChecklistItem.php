<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChecklistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checklist_category_id',
        'name',
        'price',
        'price_small',
        'price_medium',
        'price_large',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_small' => 'decimal:2',
        'price_medium' => 'decimal:2',
        'price_large' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ChecklistCategory::class, 'checklist_category_id');
    }
}
