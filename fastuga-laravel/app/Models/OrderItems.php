<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItems extends Model
{
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
            case 'W':
                return 'Waiting';
            case 'P':
                return 'Preparing';
            case 'R':
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
