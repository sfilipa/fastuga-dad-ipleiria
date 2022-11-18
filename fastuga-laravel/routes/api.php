<?php

use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\OrderItemsController;
use App\Http\Controllers\api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User Routes
Route::apiResource("users",UserController::class);

// Customer Routes
Route::apiResource("customers",CustomerController::class);
Route::get('customers/{customer}/user', [UserController::class, 'getUserOfCustomer']);

// Order Routes
Route::get('orders/status', [OrderController::class, 'getOrdersStatus']);
Route::apiResource("orders", OrderController::class);
Route::get('orders/{order}/customer', [CustomerController::class, 'getCostumerOfOrder']);
Route::get('orders/{order}/user', [UserController::class, 'getUserOfOrder']);

// OrderItems Routes
Route::apiResource("order-items", OrderItemsController::class);
Route::get('order-items/{orderItems}/user',[UserController::class, 'getUserOfOrderItems']);
Route::get('order-items/{orderItems}/order',[OrderController::class, 'getOrderOfOrderItems']);
Route::get('order-items/{orderItems}/product',[ProductController::class, 'getProductOfOrderItems']);

// Product Routes
Route::get('products/types', [ProductController::class, 'getProductsTypes']);
Route::apiResource("products", ProductController::class);
