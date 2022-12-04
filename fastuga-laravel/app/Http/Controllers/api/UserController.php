<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getUserOfOrderItems(OrderItems $orderItems) {
        return new UserResource($orderItems->user);
    }

    public function getUserOfCustomer(Customer $customer) {
        return new UserResource($customer->user);
    }

    public function getUserOfOrder(Order $order) {
        return new UserResource($order->user);
    }

    public function index()
    {
        return UserResource::collection(User::all());
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
       $request->validated();//validate password without hash
            
       $request->query->add(['password' => Hash::make($request->password)]); 

       $newUser = User::create($request->validated());

        if($request->hasFile('photo_url')){
            $path = Storage::putFile('public/fotos',  $request->file('photo_url'));
            $name = basename($path);
            $newUser["photo_url"] = $name;
            $newUser->save();
        }

        return new UserResource($newUser);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
    }

    public function destroyWithEmail(string $email)
    {
        $user = User::where('email', $email)->get();
        $user->each->delete();
    }

    public function show_me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function getAllEmployees() {
        return User::whereIn('type', array('ec', 'ed', 'em'))->get();
    }
}
