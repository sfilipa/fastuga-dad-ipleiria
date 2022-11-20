<?php

use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\OrderItemsController;
use App\Http\Controllers\api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AuthController;


/*Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();*/


Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users/me', [UserController::class, 'show_me']);
    Route::get('users/employees', [UserController::class, 'getAllEmployees']);

    // User Routes
    Route::apiResource("users", UserController::class);

    // Customer Routes
    Route::apiResource("customers", CustomerController::class);
    Route::get('customers/{customer}/user', [UserController::class, 'getUserOfCustomer']);

    // Order Routes
    Route::get('orders/status', [OrderController::class, 'getOrdersStatus']);
    Route::apiResource("orders", OrderController::class);
    Route::get('orders/{order}/customer', [CustomerController::class, 'getCostumerOfOrder']);
    Route::get('orders/{order}/user', [UserController::class, 'getUserOfOrder']);

    // OrderItems Routes
    Route::apiResource("order-items", OrderItemsController::class);
    Route::get('order-items/{orderItems}/user', [UserController::class, 'getUserOfOrderItems']);
    Route::get('order-items/{orderItems}/order', [OrderController::class, 'getOrderOfOrderItems']);
    Route::get('order-items/{orderItems}/product', [ProductController::class, 'getProductOfOrderItems']);
});

// Product Routes
Route::get('products/types', [ProductController::class, 'getProductsTypes']);
Route::apiResource("products", ProductController::class);
