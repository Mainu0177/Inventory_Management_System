<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    protected $fillable = [
        'invoice_id',
        'product_id',
        'code',
        'name',
        'description',
        'uom',
        'qty',
        'sale_price',
        'margin',
        'discount',
        'total_price',
        'user_id'
    ];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
