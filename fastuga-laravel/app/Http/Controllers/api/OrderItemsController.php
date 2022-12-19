<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateOrderItemsRequest;
use App\Http\Resources\OrderItemsResource;
use App\Models\OrderItems;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
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

    //Statistics - Chef
    public function getAllChefOrdersPrepared($user_id)
    {//flitrar por dishes
        $products_id = OrderItems::where('preparation_by', $user_id)->get('product_id');

        $allProducts = Product::whereIn('id', $products_id)->paginate(10);

        return  $allProducts;

    }

    public function getHotDishesToPrepare()
    {
        $products = [];
        $allHotOrderItems = OrderItemsResource::collection(OrderItems::whereIn('status', ['W','P'])->get());
        foreach ($allHotOrderItems as $item){
            $order = $item->order;
            if($order->status == 'P'){
                $products[] = $item;
            }
        }
        return $products;
    }

    public function updateHotDish($id, $chefId)
    {
        $orderItem = OrderItems::find($id);
        $status = $orderItem->status;
        if($status == 'R'){
            return response("Dish is already ready - invalid operation", 409);
        }

        $chef = User::find($chefId);
        if($chef == null){
            return response("Couldn't find current logged user", 403);
        }

        if($chef->type != 'EC'){
            return response("Current logged user isn't a Chef!", 403);
        }

        if($status == 'W'){
            $orderItem->status = 'P';
            $orderItem->preparation_by = $chefId;

        }else{
            $orderItem->status = 'R';
        }

        $orderItem->save();

        $order = $orderItem->order;
        if($this->checkOrderIsReady($order)){
            $order->status = 'R';
            $order->save();
        }

        return new OrderItemsResource($orderItem);
    }

    private function checkOrderIsReady(Order $order)
    {
        foreach ($order->orderItems as $item){
            if($item->status == 'W' || $item->status == 'P'){
                return false;
            }
        }
        return true;
    }
}
