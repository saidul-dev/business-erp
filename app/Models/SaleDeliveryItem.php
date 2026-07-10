<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDeliveryItem extends Model
{
    protected $fillable = [
        'sale_delivery_id',
        'sale_item_id',
        'quantity',
        'batch_no',
        'expiry_date',
        'serial_no',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'expiry_date' => 'date',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(SaleDelivery::class, 'sale_delivery_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }
}
