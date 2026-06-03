<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialStock extends Model
{
    public $timestamps = false;

    protected $fillable = ['material_id', 'qty', 'updated_at'];

    protected $casts = ['updated_at' => 'datetime'];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }
}
