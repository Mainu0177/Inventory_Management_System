<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'code',
        'description',
        'uom',
        'price',
        'unit',
        'low_stock_threshold',
        'image'
    ];

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class);
    }
}
