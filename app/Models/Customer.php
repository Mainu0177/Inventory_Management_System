<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'address',
        'user_id'
    ];
    public function products(){
        return $this->hasMany(Customer::class);
    }
}
