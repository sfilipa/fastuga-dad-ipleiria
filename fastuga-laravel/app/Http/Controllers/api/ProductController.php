<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getProductOfOrderItems(OrderItems $orderItems) {
        return new ProductResource($orderItems->product);
    }

    public function getProductsTypes()
    {
        return Product::groupBy('type')->pluck('type');
    }

    public function index()
    {
        return Product::all();
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Product $product)
    {
        return new ProductResource($product);
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
