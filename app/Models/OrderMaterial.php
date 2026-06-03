<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMaterial extends Model
{
    protected $fillable = ['order_id', 'material_id', 'name', 'qty', 'price', 'subtotal'];

    protected $casts = ['price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function material(): BelongsTo { return $this->belongsTo(Material::class); }
}
