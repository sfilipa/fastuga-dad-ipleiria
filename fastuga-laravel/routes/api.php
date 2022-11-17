<?php

use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\OrderItemsController;
use App\Http\Controllers\api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// User Routes
Route::apiResource("users",UserController::class);
Route::get('users/{user}/orders', [OrderController::class, 'getOrdersOfUser']);
Route::get('users/{user}/customer', [CustomerController::class, 'getCustomerOfUser']);
Route::get('users/{user}/order-items', [OrderItemsController::class, 'getOrderItemsOfUser']);

// Customer Routes
Route::apiResource("customers",CustomerController::class);
Route::get('customers/{customer}/user', [CustomerController::class, 'getUser']);
Route::get('customers/{customer}/orders', [CustomerController::class, 'getOrders']);
Route::get('customers/{customer}/orders/{order}', [CustomerController::class, 'getOrder']);

// Order Routes
Route::apiResource("orders", OrderController::class);
Route::get('orders/{order}/customer', [OrderController::class, 'getCostumer']);
Route::get('orders/{order}/user', [OrderController::class, 'getUser']);
Route::get('orders/{order}/orderItems', [OrderController::class, 'getOrderItems']);

// OrderItems Routes
Route::apiResource("order-items", OrderItemsController::class);

// Product Routes
Route::get('products/types', [ProductController::class, 'getProductsTypes']);
Route::apiResource("products", ProductController::class);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/orderItems', [ProductController::class, 'getOrderItems']);
Route::get('products/{product}/orderItems/{orderItem}', [ProductController::class, 'getOrderItem']);
