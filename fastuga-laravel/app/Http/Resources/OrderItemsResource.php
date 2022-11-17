<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => new OrderResource(Order::find($this->order_id)),
            'order_local_number' => $this->status,
            'product_id' => new ProductResource(Product::find($this->product_id)),
            'status' => $this->status,
            'price' => $this->price,
            'preparation_by' => new UserResource(User::find($this->preparation_by)),
            'notes' => $this->notes,
            'custom' => $this->custom,
        ];
    }
}
