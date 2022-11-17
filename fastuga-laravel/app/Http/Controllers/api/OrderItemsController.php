<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateOrderItemsRequest;
use App\Http\Resources\OrderItemsResource;
use App\Models\OrderItems;
use App\Models\User;
use Illuminate\Http\Request;

class OrderItemsController extends Controller
{
    public function index()
    {
        return OrderItems::all();
    }

    public function store(StoreUpdateOrderItemsRequest $request)
    {
        $newOrderItems =OrderItems::create($request->validated());
        return new OrderItemsResource($newOrderItems);
    }

    public function show($id)
    {
        return new OrderItemsResource(OrderItems::find($id));
    }

    public function update(StoreUpdateOrderItemsRequest $request, OrderItems $orderItems)
    {
        $orderItems->update($request->validated());
        return new OrderItemsResource($orderItems);
    }

    public function destroy(OrderItems $orderItems)
    {
        $orderItems->delete();
    }
}
