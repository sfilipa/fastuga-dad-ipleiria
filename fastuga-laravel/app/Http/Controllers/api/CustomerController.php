<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUpdateCustomerRequest;
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

    public function getCustomerByUserID($user_id) {
        return new CustomerResource(Customer::where('user_id', $user_id)->firstOrFail());
    }

    public function index()
    {
        return CustomerResource::collection(Customer::paginate(15));
    }

    public function store(StoreUpdateCustomerRequest $request)
    {
        $newCustomer = Customer::create($request->validated());
        return new CustomerResource($newCustomer);
    }

    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }

    public function update(StoreUpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer)
    {
        $customer->softDeletes();
    }

}
