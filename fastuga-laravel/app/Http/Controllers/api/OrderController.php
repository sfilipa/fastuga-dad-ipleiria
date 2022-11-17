<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemsResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function getOrderOfOrderItems(OrderItems $orderItems) {
        return new OrderResource($orderItems->order);
    }

    public function index()
    {
        return Order::all();
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
