<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'checklist_item_id', 'name', 'price', 'condition_initial', 'next_action', 'qc_status', 'qc_note'];

    protected $casts = ['price' => 'decimal:2'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function checklistItem(): BelongsTo { return $this->belongsTo(ChecklistItem::class); }
}
