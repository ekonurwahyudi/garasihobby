<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'order_date', 'customer_id', 'vehicle_id',
        'complaint', 'mileage', 'km_service', 'km_return',
        'head_mechanic', 'mechanic', 'mechanic_number',
        'subtotal', 'discount', 'other_service_price', 'total', 'status', 'created_by', 'paid_at',
        'bank_account_id', 'finance_transaction_id',
        'evidence_work_paths', 'evidence_payment_paths',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'other_service_price' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'evidence_work_paths' => 'array',
        'evidence_payment_paths' => 'array',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(OrderItem::class); }
    public function materials(): HasMany { return $this->hasMany(OrderMaterial::class); }
    public function financeTransaction(): BelongsTo { return $this->belongsTo(FinanceTransaction::class); }

    public function scopeStatus($q, string $s) { return $q->where('status', $s); }
    public function scopeToday($q) { return $q->whereDate('order_date', today()); }
}
