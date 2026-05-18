<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryChallan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'challan_no',
        'pr_no',
        'quotation_no',
        'po_no',
        'status',
        'created_by',
        'delivered_at'
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliveryChallanProducts()
    {
        return $this->hasMany(DeliveryChallanProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
