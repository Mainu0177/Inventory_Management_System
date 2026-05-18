<?php

namespace App\Models;

use App\Models\User;
use App\Models\Customer;
use App\Models\InvoiceProduct;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'quotation_no',
        'total',
        'discount',
        'vat',
        'payable',
        'poNumber',
        'prNumber',
        'status',
        'paid_amount',
        'user_id',
        'created_by',
        'customer_id'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function customer(){
        return $this->belongsTo(Customer::class);
    }

    public function invoiceProducts(){
        return $this->hasMany(InvoiceProduct::class);
    }
}
