<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'plate_number', 'brand', 'model', 'vehicle_size', 'photo_path', 'year'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
