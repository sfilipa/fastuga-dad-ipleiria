<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'photo_url',
        'price'
    ];

    public function orderItems() {
        return $this->hasMany(OrderItems::class,'product_id');
    }
}
