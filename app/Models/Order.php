<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
