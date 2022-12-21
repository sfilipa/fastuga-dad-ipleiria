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

        $allProducts = Product::whereIn('id', $products_id)->where('type', 'hot dish')->paginate(10);

        return  $allProducts;

    }

    public function getHotDishesToPrepare($chefId)
    {
        $products = [];
        $chef = User::find($chefId);
        if($chef == null){
            return response("Couldn't find current logged user", 403);
        }

        if($chef->type != 'EC'){
            return response("Current logged user isn't a Chef!", 403);
        }

        $allHotOrderItems = OrderItemsResource::collection(
            OrderItems::whereIn('status', ['W','P'])
                ->where(function ($query) use ($chefId) {
                    $query->where('preparation_by', null)
                          ->orWhere('preparation_by', $chefId);
                })
                ->get()
            );
        foreach ($allHotOrderItems as $item){
            $order = $item->order;
            if($order->status == 'P'){
                $products[] = $item;
            }
        }
        return $products;
    }

    public function updateHotDish(Request $request, $id)
    {
        $orderItemFound = OrderItems::find($id);
        if($orderItemFound == null){
            return response("Couldn't find the order item", 404);
        }
        $status = $orderItemFound->status;
        if($status == 'R'){
            return response("Dish is already ready - invalid operation", 409);
        }

        $userId = $request['userId'];
        $chef = User::find($userId);
        if($chef == null){
            return response("Couldn't find current logged user", 403);
        }

        if($chef->type != 'EC'){
            return response("Current logged user isn't a Chef!", 403);
        }

        if($status == 'W'){
            $orderItemFound->status = 'P';
            $orderItemFound->preparation_by = $userId;
        }else{
            //Backend validation so that only the same chef that changes from 'W' to 'P', can change from 'P' to 'R'
            if($orderItemFound->preparation_by != $userId){
                return response("This dish is being prepared by another chef!", 403);
            }
            $orderItemFound->status = 'R';
        }

        $orderItemFound->save();

        return new OrderItemsResource($orderItemFound);
    }
}
