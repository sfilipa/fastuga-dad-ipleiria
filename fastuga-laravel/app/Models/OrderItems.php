<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'order_local_number',
        'product_id',
        'status',
        'price',
        'preparation_by',
        'notes',
        'custom'
    ];

    public function getStatus()
    {
        switch ($this->status) {
            case 'w':
                return 'Waiting';
            case 'p':
                return 'Preparing';
            case 'r':
                return 'Ready';
        }
    }

    public function user() {
        return $this->belongsTo(User::class, 'preparation_by');
    }

    public function order() {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product() {
        return $this->belongsTo(Product::class,'product_id');
    }
}
