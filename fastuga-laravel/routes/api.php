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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('users/me', [UserController::class, 'show_me']);
});

// User Routes
Route::get('users/employees', [UserController::class, 'getAllEmployees']);//->middleware('can:view, App\Models\User'); - so o manager consegue ver os employees assim
Route::apiResource("users", UserController::class);

// Customer Routes
Route::get('customers/{customer}/user', [UserController::class, 'getUserOfCustomer']);
Route::apiResource("customers", CustomerController::class);

// Order Routes

Route::prefix('orders')->group(function () {
    Route::get('/status', [OrderController::class, 'getOrdersStatus']);
    Route::get('/statusTAES/{status}', [OrderController::class, 'getOrderByStatusTAES']);
    Route::get('/status/{status}', [OrderController::class, 'getOrderByStatus']);
    Route::get('/{order}/customer', [CustomerController::class, 'getCostumerOfOrder']);
    Route::get('/{order}/user', [UserController::class, 'getUserOfOrder']);
});
Route::post('ordersTAES',[OrderController::class, 'storeTAES']);
Route::get('unassignedOrders', [OrderController::class, 'getUnassignedOrders']);
Route::apiResource("orders", OrderController::class);

// OrderItems Routes
Route::prefix('order-items')->group(function () {
    Route::get('/{orderItems}/user', [UserController::class, 'getUserOfOrderItems']);
    Route::get('/{orderItems}/order', [OrderController::class, 'getOrderOfOrderItems']);
    Route::get('/{orderItems}/product', [ProductController::class, 'getProductOfOrderItems']);
});
Route::apiResource("order-items", OrderItemsController::class);

// Product Routes
Route::get('products/types', [ProductController::class, 'getProductsTypes']);
Route::apiResource("products", ProductController::class);
