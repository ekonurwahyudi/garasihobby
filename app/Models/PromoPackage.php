<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoPackage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_small',
        'price_medium',
        'price_large',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_small' => 'decimal:2',
        'price_medium' => 'decimal:2',
        'price_large' => 'decimal:2',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];
}
