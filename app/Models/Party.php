<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    public const OPENING_BALANCE_TYPES = ['due', 'advance'];

    protected $fillable = [
        'is_customer',
        'is_supplier',
        'is_company',
        'name',
        'contact_person',
        'designation',
        'phone',
        'email',
        'address',
        'nid_no',
        'bin_no',
        'tin_no',
        'credit_limit',
        'credit_days',
        'opening_balance',
        'opening_balance_type',
        'status',
        'notes',
    ];

    protected $casts = [
        'is_customer' => 'boolean',
        'is_supplier' => 'boolean',
        'is_company' => 'boolean',
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
        'opening_balance' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function getRoleLabelAttribute(): string
    {
        return match (true) {
            $this->is_customer && $this->is_supplier => 'Customer & Supplier',
            $this->is_supplier => 'Supplier',
            default => 'Customer',
        };
    }
}
