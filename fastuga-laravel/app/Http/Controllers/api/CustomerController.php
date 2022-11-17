<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Cassandra\Custom;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function getCostumerOfOrder(Order $order) {
        return new CustomerResource(optional($order->customer));
    }

    public function index()
    {
        return Customer::all();
    }

    public function store(Request $request)
    {
        //
    }

    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
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
