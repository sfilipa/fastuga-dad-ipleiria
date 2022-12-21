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
    public function __construct()
    {
        $this->middleware('auth.manager', ['except' => [
            'getCostumerOfOrder',
            'getCustomerByUserID',
            'store',
            'show',
            'show_me',
            'store',
            'update',
            'destroy',
        ]]);
    }

    public function getCostumerOfOrder(Order $order) {
        return new CustomerResource(optional($order->customer));
    }

    public function getCustomerByUserID($user_id) {
        return new CustomerResource(Customer::where('user_id', $user_id)->firstOrFail());
    }

    public function index(Request $request)
    {
        // dd(Customer::query()->join('users', 'customers.user_id', '=', 'users.id'));
        $query = Customer::query()
            ->join('users', 'customers.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->select('users.name', 'users.type' , 'users.blocked', 'users.custom', 'users.id', 'users.email', 'users.photo_url', 'customers.*');
        $name = $request->query('name');
        if($name != null){
            $query->where('users.name','LIKE','%'.$name.'%');
        }

        $email = $request->query('email');
        if($email != null){
            $query->where('users.email','LIKE','%'.$email.'%');
        }

        $nif = $request->query('nif');
        if($nif != null){
            $query->where('customers.nif',$nif);
        }

        return $query->orderBy('customers.id','ASC')->paginate(15);
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
        $customer = Customer::findOrFail($customer->id);
        $customer->delete();
    }

}
