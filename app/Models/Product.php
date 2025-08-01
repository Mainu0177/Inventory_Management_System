<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = [
        'user_id',
        'category_id',
        'name',
        'price',
        'unit',
        'image',
    ];
}
