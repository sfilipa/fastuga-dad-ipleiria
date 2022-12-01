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

    public function getOrderByStatus(String $status)
    {
        $status = strtoupper($status);
        if ($status == 'P' or  $status == 'R' or $status == 'D' or $status == 'C') {
            return Order::where('status', $status)->with("orderItems", "orderItems.product")->get();
        }
    }

    public function getOrderByStatusTAES(String $status)
    {
        $status = strtoupper($status);
        if ($status == 'P' or  $status == 'R' or $status == 'D' or $status == 'C') {
            return OrderResource::collection(Order::where('status', $status)->get()); 
        }
    }

    public function getOrderOfOrderItems(OrderItems $orderItems)
    {
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
        $order->fill($request->validated());
        $order->custom = json_encode($request["custom"]);
        $order->save();
        return new OrderResource($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
    }
}
