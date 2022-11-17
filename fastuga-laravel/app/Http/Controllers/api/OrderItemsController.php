<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemsResource;
use App\Models\OrderItems;
use App\Models\User;
use Illuminate\Http\Request;

class OrderItemsController extends Controller
{
    public function getOrderItemsOfUser(User $user) {
        return OrderItemsResource::collection($user->orderItems->sortByDesc('id'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        return OrderItems::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param OrderItems $orderItems
     * @return OrderItemsResource
     */
    public function show(OrderItems $orderItems)
    {
        return new OrderItemsResource($orderItems);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
