<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A delivery area + flat charge shown to the customer at checkout (e.g.
 * "Inside Dhaka — 60"). Informational only: the picked zone's name/charge
 * are snapshotted onto the Sale for the admin/courier's reference, but
 * never added to the order's subtotal_amount/total_amount or the ledger —
 * this store doesn't collect delivery fees itself.
 */
class DeliveryZone extends Model
{
    protected $fillable = [
        'name',
        'charge',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'charge' => 'decimal:2',
        'sort_order' => 'integer',
        'status' => 'boolean',
    ];
}
