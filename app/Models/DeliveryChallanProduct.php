<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChallanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_challan_id',
        'product_id',
        'code',
        'name',
        'description',
        'uom',
        'qty',
        'unit_price',
        'margin',
        'discount',
        'total_price'
    ];

    public function deliveryChallan()
    {
        return $this->belongsTo(DeliveryChallan::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
