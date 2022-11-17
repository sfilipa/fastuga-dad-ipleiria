<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'description',
        'photo_url',
        'price'
    ];

    protected $types = array('type');

    public function orderItems() {
        return $this->hasMany(OrderItems::class,'product_id');
    }
}
