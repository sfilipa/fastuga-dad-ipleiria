<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateOrderRequest;
use App\Http\Resources\OrderItemsResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\User;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function getOrdersStatus()
    {
        return Order::groupBy('status')->pluck('status');
    }

    public function getOrderOfOrderItems(OrderItems $orderItems) {
        return new OrderResource($orderItems->order);
    }

    public function index()
    {
        return Order::all();
    }

    public function store(StoreUpdateOrderRequest $request)
    {
        $newOrder = Order::create($request->validated());
        return new OrderResource($newOrder);
    }

    public function show(Order $order)
    {
        return new OrderResource($order);
    }

    public function update(StoreUpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());
        return new OrderResource($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
    }
}
