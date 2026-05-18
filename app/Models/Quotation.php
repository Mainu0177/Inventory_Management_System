<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'quotation_no',
        'pr_no',
        'total_amount',
        'status',
        'valid_until',
        'terms',
        'created_by'
    ];

    protected $casts = [
        'valid_until' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotationProducts()
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
