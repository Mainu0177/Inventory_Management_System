<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'contact_person',
        'tax_id',
        'shipping_address',
        'user_id',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
