<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];

    public function assetPurchases(): HasMany
    {
        return $this->hasMany(AssetPurchase::class);
    }
}
