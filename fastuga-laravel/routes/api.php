<?php

use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\OrderController;
use App\Http\Controllers\api\OrderItemsController;
use App\Http\Controllers\api\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AuthController;
use App\Policies\UserPolicy;


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
Route::put('users/blockUnblock/{user}', [UserController::class, 'blockUnblockUser']);
Route::put('users/updatePasswordTAES/{email}', [UserController::class, 'updateTAESPassword']);
Route::put('users/updateNameTAES/{email}', [UserController::class, 'updateTAESName']);
Route::patch('users/{user}/password', [UserController::class, 'update_password'])/*->middleware('can:updatePassword, App\Models\User')*/;
Route::apiResource("users", UserController::class);

// Customer Routes
Route::get('customers/{customer}/user', [UserController::class, 'getUserOfCustomer']);
Route::get('customers/user/{user_id}', [CustomerController::class, 'getCustomerByUserID']);
Route::get('customers/byEmail/{email}', [CustomerController::class, 'getCustomerByEmail']);
Route::get('customers/byNif/{nif}', [CustomerController::class, 'getCustomerByNif']);
Route::apiResource("customers", CustomerController::class);

// Order Routes

Route::prefix('orders')->group(function () {
    Route::get('/active', [OrderController::class, 'getNumberOfActiveOrders']);
    Route::get('/status', [OrderController::class, 'getOrdersStatus']);
    Route::get('/statusTAES', [OrderController::class, 'getOrderByStatusTAES']);
    Route::get('/delivery', [OrderController::class, 'getOrderForDelivery']);
    Route::get('/status/{status}', [OrderController::class, 'getOrderByStatus']);
    Route::get('/{order}/customer', [CustomerController::class, 'getCostumerOfOrder']);
    Route::get('/{order}/user', [UserController::class, 'getUserOfOrder']);
    Route::put('/{order}/cancel', [OrderController::class, 'cancelOrder']);
    Route::patch('/{order}/{status}', [OrderController::class, 'updateOrderStatus']);
    Route::get('/customer/{user_id}', [OrderController::class, 'getAllCustomerOrders']);
    Route::get('/current/customer/{user_id}', [OrderController::class, 'getCustomerCurrentOrders']);
    Route::get('/order/orderItems/{order_id}', [OrderController::class, 'getAllOrderProducts']);
    Route::get('/delivered/{user_id}', [OrderController::class, 'getAllOrdersDelivered']);//statistics - driver
    Route::get('/bymonth/total', [OrderController::class, 'getTotalOrdersByMonth']);//statistics - managers
    Route::get('/bymonth', [OrderController::class, 'getTotalOrdersMonths']);//statistics - managers
    Route::get('/customer/{user_id}', [OrderController::class, 'getAllCustomerOrders']);//statistics - customers
    Route::get('/{order_id}/products', [OrderController::class, 'getAllOrderProducts']);//statistics - customers
    Route::get('/{order_id}/orderItems', [OrderController::class, 'getItemsAndProducts']);
});
Route::post('ordersTAES',[OrderController::class, 'storeTAES']);
Route::get('unassignedOrders', [OrderController::class, 'getUnassignedOrders']);
Route::apiResource("orders", OrderController::class);

// OrderItems Routes

Route::get('/order-items/prepared/{user_id}', [OrderItemsController::class, 'getAllChefOrdersPrepared']);
Route::prefix('order-items')->group(function () {
    Route::get('/{orderItems}/user', [UserController::class, 'getUserOfOrderItems']);
    Route::get('/{orderItems}/order', [OrderController::class, 'getOrderOfOrderItems']);
    Route::get('/{orderItems}/product', [ProductController::class, 'getProductOfOrderItems']);
    Route::get('/hotdishes/{chefId}', [OrderItemsController::class, 'getHotDishesToPrepare']);
    Route::patch('/{id}', [OrderItemsController::class, 'updateHotDish']);
});
Route::apiResource("order-items", OrderItemsController::class);

// Product Routes
Route::get('products/types', [ProductController::class, 'getProductsTypes']);

//Statistics
Route::get('/products/top', [ProductController::class, 'getBestProducts']);
Route::get('/products/top/total', [ProductController::class, 'getTotalOrdersOfTopProducts']);
Route::get('/products/worst', [ProductController::class, 'getWorstProducts']);
Route::get('/products/worst/total', [ProductController::class, 'getTotalOrdersOfWorstProducts']);

Route::apiResource("products", ProductController::class);
