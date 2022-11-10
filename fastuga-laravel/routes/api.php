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
Route::get('users', [UserController::class, 'index']);
Route::get('users/{user}', [UserController::class, 'index']);
Route::get('users/{user}/orders', [UserController::class, 'getOrders']);
Route::get('users/{user}/customer', [UserController::class, 'getCustomer']);
Route::get('users/{user}/orderItems', [UserController::class, 'getOrderItems']);

// Customer Routes
Route::get('customers', [CustomerController::class, 'index']);
Route::get('customers/{customer}', [CustomerController::class, 'getCustomer']);
Route::get('customers/{customer}/user', [CustomerController::class, 'getUser']);
Route::get('customers/{customer}/orders', [CustomerController::class, 'getOrders']);
Route::get('customers/{customer}/orders/{order}', [CustomerController::class, 'getOrder']);

// Order Routes
Route::get('orders', [OrderController::class, 'index']);
Route::get('orders/{order}', [OrderController::class, 'getOrder']);
Route::get('orders/{order}/customer', [OrderController::class, 'getCostumer']);
Route::get('orders/{order}/user', [OrderController::class, 'getUser']);
Route::get('orders/{order}/orderItems', [OrderController::class, 'getOrderItems']);

// OrderItems Routes
Route::get('orderItems', [OrderItemsController::class, 'index']);
Route::get('orderItems/{orderItem}', [OrderItemsController::class, 'getOrderItems']);

// Product Routes
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'getProduct']);
Route::get('products/{product}/orderItems', [ProductController::class, 'getOrderItems']);
Route::get('products/{product}/orderItems/{orderItem}', [ProductController::class, 'getOrderItem']);
