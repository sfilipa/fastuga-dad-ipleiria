<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'points',
        'nif',
        'default_payment_type',
        'default_payment_reference'
    ];

    public function user() {
        return $this->belongsTo(User::class,'user_id');
    }

    public function orders() {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
