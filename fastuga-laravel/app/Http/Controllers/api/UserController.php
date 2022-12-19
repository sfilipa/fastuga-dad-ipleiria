<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\App;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreUpdateCustomerRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Http\Requests\UpdateUserNameTAESRequest;
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

    public function update(UpdateUserRequest $userRequest, User $user)
    {

        $customerRequest = new StoreUpdateCustomerRequest($userRequest->all());
        DB::beginTransaction();

        try {

            $validatedData = $userRequest->validated();
            //$user->update($userRequest->validated());

            if($userRequest->hasFile('photo_url')){
                // Delete Existing Photo
                if(Storage::disk('public')->exists('fotos/'.$user->photo_url)) {
                    Storage::disk('public')->delete('fotos/'.$user->photo_url);
                }
                // Save New Photo
                $path = Storage::putFile('public/fotos',  $userRequest->file('photo_url'));
                $name = basename($path);
                $validatedData["photo_url"] = $name;
            }

            $user->fill($validatedData);
            $user->save();
            $responseUser = new UserResource($user);

            if($user->type == 'C'){
                $customerRequest->validate($customerRequest->rules());
                $customer = $user->customer;
                $customer->update($customerRequest->all());
                $responseCustomer = new CustomerResource($customer);
            }
            DB::commit();
            return response()->json("Good", 204);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 400);
        }
    }

    public function blockUnblockUser(UpdateUserRequest $userRequest, User $user)
    {
        DB::beginTransaction();
        try {

            $validatedData = $userRequest->validated();
            if($userRequest->hasFile('photo_url')){
                // Delete Existing Photo
                if(Storage::disk('public')->exists('fotos/'.$user->photo_url)) {
                    Storage::disk('public')->delete('fotos/'.$user->photo_url);
                }
                // Save New Photo
                $path = Storage::putFile('public/fotos',  $userRequest->file('photo_url'));
                $name = basename($path);
                $validatedData["photo_url"] = $name;
            }

            $user->fill($validatedData);
            $user->save();
            $responseUser = new UserResource($user);
            DB::commit();
            return response()->json("Good", 204);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 400);
        }
    }

    public function updateTAESPassword(UpdateUserPasswordRequest $request, string $email)
    {
            try {

            $user = User::where('email', $email)->firstOrFail();

            $userpassword = $request->validated();

            User::whereId($user->id)->update([
                'password' => Hash::make($request->password)
            ]);

            return new UserResource($user);
        } catch (\Exception $e) {
        return response()->json($e->getMessage(), 400);
    }
    }
    public function updateTAESName(UpdateUserNameTAESRequest $request, string $email)
    {
        try {

            $user = User::where('email', $email)->firstOrFail();

            $userName = $request->validated();

            User::whereId($user->id)->update([
                'name' => $request->name
            ]);

            return new UserResource($user);
        }catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function destroy(User $user)
    {
        $user = User::find($user->id);
        $user->delete();
    }

    public function destroyWithEmail(string $email)
    {
        $user = User::where('email', $email)->get();

        if($user[0]->type == 'C'){
            $customer = Customer::where('user_id', $user[0]->id)->get();
            $customer->each->delete();
        }
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